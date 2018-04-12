var data = drupalSettings.data;
(function ($, Drupal, drupalSettings) {
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
        events: data,
        eventRender: function eventRender( event, element, view ) {
          // Prepare the bootstrap tooltip.
          $(element).attr('data-html', true); // Allows HTML on tooltip.
          $(element).tooltip({title: '<h3>' + event.title + '</h3><br />' +
                '<h4>Creado: ' + event.created + '<br /><br />' + 'Autor: ' + event.author + '<br /><br />' + 'Departamento: ' + event.dept + '<br /><br />' + 'Tipo de evento: ' + event.type + '<br /><br />' +
                '</h4>', 'placement': 'top'});

          // Filter by department tid.
          var dept = $('#edit-departamento').val();
          // If dropdown is set to show all (no filtering) it sends a 0.
          if (dept > 0) {
            return ['all', event.dept_id].indexOf($('#edit-departamento').val()) >= 0
          }

          // Filter by worker uid.
          var worker_uid = $('#edit-trabajador').val();
          if (worker_uid > 0) {
            return ['all', event.author_uid].indexOf($('#edit-trabajador').val()) >= 0
          }
        },
        eventColor: '#378006'
      });

      // On filters change rerender the calendar to take into account passed in filter value.
      $('#edit-departamento').on('change',function(){
        $('#calendar').fullCalendar('rerenderEvents');
      });
      $('#edit-trabajador').on('change',function(){
        $('#calendar').fullCalendar('rerenderEvents');
      });

    }
  }
})(jQuery, Drupal);