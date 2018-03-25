var data = drupalSettings.data;
(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.custom = {
    attach: function (context, settings) {
      // var tooltip = $('<div/>').qtip({
      //   id: 'fullcalendar',
      //   prerender: true,
      //   content: {
      //     text: ' ',
      //     title: {
      //       button: true
      //     }
      //   },
      //   position: {
      //     my: 'bottom center',
      //     at: 'top center',
      //     target: 'mouse',
      //     viewport: $('#calendar'),
      //     adjust: {
      //       mouse: false,
      //       scroll: false
      //     }
      //   },
      //   show: false,
      //   hide: false,
      //   style: 'qtip-light'
      // }).qtip('api');

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
        eventColor: '#378006',
        eventClick: function(data, event, view) {
          var content = '<h3>'+data.title+'</h3>' +
            '<p><b>Start:</b> '+data.start+'<br />' +
            '</p>';

          tooltip.set({
            'content.text': content
          })
            .reposition(event).show(event);
        },
      });
      $('#edit-departamento').on('change',function(){
        $('#calendar').fullCalendar('rerenderEvents');
      })
    }
  }
})(jQuery, Drupal);