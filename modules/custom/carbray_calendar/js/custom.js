(function ($, Drupal) {
  Drupal.behaviors.custom = {
    attach: function (context, settings) {
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
        events: [
          {
            title: 'All Day Event',
            start: '2018-01-01',
            dept: '1'
          },
          {
            title: 'Long Event',
            start: '2018-01-07',
            end: '2018-01-10',
            dept: '1',
          },
          {
            id: 999,
            title: 'Repeating Event',
            start: '2018-01-09T16:00:00'
          },
          {
            id: 999,
            title: 'Repeating Event',
            start: '2018-01-16T16:00:00'
          },
          {
            title: 'Conference',
            start: '2018-01-11',
            end: '2018-01-13'
          },
          {
            title: 'Meeting',
            start: '2018-01-12T10:30:00',
            end: '2018-01-12T12:30:00',
            // rendering: 'background'
          },
          {
            title: 'Pedido extra',
            start: '2018-01-12T10:30:00',
            end: '2018-01-12T13:30:00',
            // rendering: 'background'
          },
          {
            title: 'Lunch',
            start: '2018-01-12T12:00:00',
            dept: '1',
          },
          {
            title: 'Meeting',
            start: '2018-01-12T14:30:00',
            dept: '2'
          },
          {
            title: 'Happy Hour',
            start: '2018-01-12T17:30:00',
            dept: '3'
          },
          {
            title: 'Dinner',
            start: '2018-01-12T20:00:00'
          },
          {
            title: 'Birthday Party',
            start: '2018-01-13T07:00:00'
          },
          {
            title: 'Click for Google',
            url: 'http://google.com/',
            start: '2018-01-28'
          }
        ],
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