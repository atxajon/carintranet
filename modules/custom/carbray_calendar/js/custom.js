var data = drupalSettings.data;
console.log(data);
(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.custom = {
    attach: function (context, settings) {



      var events = [
        {
          title: 'All Day Event',
          start: '2018-03-11',
          dept: '185'
        },
        {
          title: 'Long Event',
          start: '2018-03-07',
          end: '2018-03-10',
          dept: '186',
        },
        {
          id: 999,
          title: 'Repeating Event',
          start: '2018-03-19T16:00:00'
        },
        {
          id: 999,
          title: 'Repeating Event',
          start: '2018-03-16T16:00:00'
        },
        {
          title: 'Conference',
          start: '2018-03-11',
          end: '2018-03-13'
        },
        {
          title: 'Meeting',
          start: '2018-03-12T10:30:00',
          end: '2018-03-12T12:30:00',
          // rendering: 'background'
        },
        {
          title: 'Pedido extra',
          start: '2018-03-20T10:30:00',
          end: '2018-03-20T13:30:00',
          // rendering: 'background'
        },
        {
          title: 'Lunch',
          start: '2018-03-21T12:00:00',
          dept: '186',
        },
        {
          title: 'Meeting',
          start: '2018-03-22T14:30:00',
          dept: '189'
        },
        {
          title: 'Happy Hour',
          start: '2018-03-24T17:30:00',
          dept: '188'
        },
        {
          title: 'Dinner',
          start: '2018-03-26T20:00:00',
          dept: '189'
        },
        {
          title: 'Birthday Party',
          start: '2018-03-23T07:00:00',
          dept: '189'
        },
        {
          title: 'Click for Google',
          url: 'http://google.com/',
          start: '2018-03-28',
          dept: '187'
        }
      ];

      $('#calendar').fullCalendar({
        header: {
          left: 'prev,next today',
          center: 'title',
          right: 'agendaDay,listDay,agendaWeek,month,listMonth'
        },
        // defaultDate: '2018-01-20',
        editable: false,
        eventLimit: true, // allow "more" link when too many events
        navLinks: true, // can click day/week names to navigate views
        events: data,
        // If dropdown is set to show all (no filtering) it sends a 0, hence only showing school: 0 events...
        // Need to investigate how to make the 0 into 'all'...
        eventRender: function eventRender( event, element, view ) {
          var dept = $('#edit-departamento').val();
          if (dept > 0) {
            return ['all', event.dept].indexOf($('#edit-departamento').val()) >= 0
          }
        },
        eventColor: '#378006'
      });
      $('#edit-departamento').on('change',function(){
        $('#calendar').fullCalendar('rerenderEvents');
      })
    }
  }
})(jQuery, Drupal);