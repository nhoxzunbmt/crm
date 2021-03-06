/* Initialize FullCalendar */
var date = new Date();
var d = date.getDate();
var m = date.getMonth();
var y = date.getFullYear();
$('#calendar').fullCalendar({
    header: {
        left: 'prev,next',
        center: 'title',
        right: 'month,agendaWeek,agendaDay'
    },
    firstDay: 6,
    hiddenDays: [ 4, 5 ],
    events: config.routes[0].plan,
    eventOrder: 'color',
    defaultView : 'month'
});
