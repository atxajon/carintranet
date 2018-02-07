(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.carbray_timer = {
    attach: function (context, settings) {
      var packMinutos = drupalSettings.pack_minutos;
      var timeAlert = false;

      $('#edit-start', context).once('carbray_timer').on('click', function () {
        if (packMinutos) {
          $('#crono').timer({
            format: '%H:%M:%S',
            countdown: true,
            duration: packMinutos,
          });
          alertPopup();
        }
        else {
          $('#crono').timer({
            format: '%H:%M:%S'
          });
        }
        $('#edit-pause').removeClass('hidden');
        $('#edit-start').addClass('hidden');
        return false;
      });

      $('#edit-pause', context).once('carbray_timer').on('click', function () {
        $('#crono').timer('pause');
        $('#edit-resume').removeClass('hidden');
        $('#edit-pause').addClass('hidden');
        // Get total run of seconds.
        var seconds = $("#crono").data('seconds');
        var minutes = Math.floor(seconds / 60);
        document.getElementById('edit-timer').value = minutes;
        $('#edit-timer').removeClass('hidden');
        return false;
      });

      $('#edit-resume', context).once('carbray_timer').on('click', function () {
        $('#crono').timer('resume');
        $('#edit-pause').removeClass('hidden');
        $('#edit-resume').addClass('hidden');
        $('#edit-timer').addClass('hidden');
        alertPopup();
        return false;
      });

      // Run a recurring check every 60 seconds to test if the timer countdown value left
      // is less than 2 hours (7200 seconds).
      function alertPopup() {
        var timerId = setInterval(function () {
          var total = $('#crono').data('seconds');
          if (total <= 7200) {
            if (timeAlert === false) {
              alert('Aviso: quedan menos de dos horas antes de que el pack de horas se agote.');
              timeAlert = true;
              clearInterval(timerId);
            }
          }
        }, 60000);
      }
    }
  }

  Drupal.behaviors.delete_actuacion = {
    attach: function (context, settings) {
      $('.delete-actuacion').on( "click", function() {
        if (confirm('Atención: esto eliminará la actuacion. Quieres proceder?')) {
          $(this).parent('.carbray-edit-actuacion').submit();
        } else {
          return false;
        }
      });
    }
  }

})(jQuery, Drupal, drupalSettings);