<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AnnouncementRequest;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Announcement;

class AnnouncementController extends Controller
{
    public function listAll()
    {
        $announcements = Announcement::all();
        $dataView 	= [
            'announcements'	=>	 $announcements
        ];

        return view('admin.announcement.list', $dataView);
    }

    public function create()
    {
        return view('admin.announcement.create');
    }

    public function doCreate(AnnouncementRequest $request)
    {
        $announcement           =   new Announcement;
        $announcement->month    =   \Config::get('app.current_month');
        $announcement->text     =   $request->text;
        $announcement->start    =   $request->start;

        try {
            $announcement->save();
            return redirect()->back()->with('message','Announcement has been added successfully !');
        } catch (ParseException $ex) {
            echo 'Failed to create new announcement , with error message: ' . $ex->getMessage();
        }
    }

    public function update($id)
    {
        $announcement = Announcement::findOrFail($id);
        $dataView 	= [
            'announcement'	=>  $announcement
        ];

        return view('admin.announcement.edit', $dataView);
    }

    public function doUpdate(AnnouncementRequest $request, $id)
    {
        $announcement           =   Announcement::findOrFail($id);
        $announcement->month    =   \Config::get('app.current_month');
        $announcement->text     =   $request->text;
        $announcement->start    =   $request->start;
        try {
            $announcement->save();
            return redirect()->route('announcements')->with('message','Announcement has been updated successfully !');
        } catch (ParseException $ex) {
            echo 'Failed to update announcement , with error message: ' . $ex->getMessage();
        }
    }

    public function doDelete($id)
    {
        $announcement  =   Announcement::findOrFail($id);

        try {
            $announcement->delete();
            return redirect()->back()->with('message','Announcement has been deleted successfully !');
        } catch (ParseException $ex) {
            echo 'Failed to delete announcement , with error message: ' . $ex->getMessage();
        }
    }
}