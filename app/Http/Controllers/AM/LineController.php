<?php

namespace App\Http\Controllers\AM;


use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EditLineRequest;
use App\Http\Requests\Admin\MRAchievementRequest;
use App\Line;
use App\Employee;
use App\MrLines;
use Redirect;
use App\Customer;
use App\Report;
use App\Product;

class LineController extends Controller
{
    public function listAll()
    {
        $lines = Line::all();
        $dataView 	= [
            'lines'	=>	 $lines
        ];

        return view('am.line.list', $dataView);
    }


    public function single($mr, $currentMonth)
    {
        $actualVisits               =   [];
        $MonthlyCustomerProducts    =   [];
        $MRLine                     =   [];
        $doctors                    =   Customer::where('mr_id', $mr)->get();

        foreach($doctors as $singleDoctor)
        {
            $actualVisits [$singleDoctor->id] = Report::where('mr_id', $mr)
                ->where('month', $currentMonth)
                ->where('doctor_id', $singleDoctor->id)
                ->count();
            $MonthlyCustomerProducts[$singleDoctor->id]    =   Customer::monthlyProductsBought([$singleDoctor->id])->toArray();
        }


        $products                   =   Product::where('line_id', Employee::findOrFail($mr)->line_id)->get();
        $coverageStats              =   Employee::coverageStats($mr, $currentMonth);
        $allManagers                =   Employee::yourManagers($mr);
        $totalProducts              =   Employee::monthlyDirectSales($mr, $currentMonth);

        $totalSoldProductsSales     =   $totalProducts['totalSoldProductsSales'];
        $totalSoldProductsSalesPrice=   $totalProducts['totalSoldProductsSalesPrice'];
        $currentMonth               =   \Carbon\Carbon::parse($currentMonth);
        $lines = MrLines::select('line_id', 'from', 'to')->where('mr_id', $mr)->get();

        foreach($lines as $line) {
            $lineFrom = \Carbon\Carbon::parse($line->from);
            $lineTo = \Carbon\Carbon::parse($line->to);

            if (!$currentMonth->lte($lineTo) && $currentMonth->gte($lineFrom) )
            {
                $MRLine = MrLines::where('mr_id', $mr)->where('line_id', $line->line_id)->get();
            }
        }

        $dataView  = [
            'doctors'                       =>  $doctors,
            'MonthlyCustomerProducts'       =>  $MonthlyCustomerProducts,
            'actualVisits'                  =>  $actualVisits,
            'products'                      =>  $products,
            'totalVisitsCount'              =>  $coverageStats['totalVisitsCount'],
            'actualVisitsCount'             =>  $coverageStats['actualVisitsCount'],
            'totalMonthlyCoverage'          =>  $coverageStats['totalMonthlyCoverage'],
            'allManagers'                   =>  $allManagers,
            'totalSoldProductsSales'        =>  $totalSoldProductsSales,
            'totalSoldProductsSalesPrice'   =>  $totalSoldProductsSalesPrice,
            'MRLines'                       =>  $MRLine
        ];
        return view('am.line.single', $dataView);
    }

    public function update($id)
    {
        $line = Line::findOrFail($id);
        $dataView 	= [
            'line'	=>  $line
        ];

        return view('am.line.edit', $dataView);
    }

    public function doUpdate(EditLineRequest $request, $id)
    {
        $line   =   Line::findOrFail($id);
        $line->title = $request->title;


        try {
            $line->save();
            return redirect()->route('lines')->with('message','Line has been updated successfully !');
        } catch (ParseException $ex) {
            echo 'Failed to update this line , with error message: ' . $ex->getMessage();
        }
    }

    public function doDelete($id)
    {
        $line  =   Line::findOrFail($id);

        try {
            $line->delete();
            return redirect()->back()->with('message','Line has been deleted successfully !');
        } catch (ParseException $ex) {
            echo 'Failed to delete this line , with error message: ' . $ex->getMessage();
        }
    }

    public function achievement()
    {
        $MRs        = Employee::where('manager_id', \Auth::user()->id)->get();
        $dataView   = [
            'MRs'       =>  $MRs
        ];
        return view('am.line.history.search', $dataView);
    }

    public function doAchievement(MRAchievementRequest $request)
    {


        $year   =   $request->year;
        $month  =   $request->month;
        $mr     =   $request->mr;

        $month = \Carbon\Carbon::parse($month.'-'.$year);


        return Redirect::route('amSingleLine', array($mr, \Carbon\Carbon::parse($month)->format('M-Y')));
    }
}
