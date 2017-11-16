(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.carbray_timer = {
    attach: function (context, settings) {

      var inicio = 0;
      var timeout = 0;
      var total_minutes = 0;
      $('.form-item-timer').hide();

      $('#edit-start', context).once('carbray_timer').on('click', function () {
        empezarDetener(this);
        return false;
      });

      function empezarDetener(elemento) {
        if (timeout == 0) {
          // empezar el cronometro
          document.getElementById('edit-start').innerHTML = "Detener";
          $('#edit-start').addClass('btn-warning');

          // Obtenemos el valor actual
          inicio = vuelta = new Date().getTime();

          // iniciamos el proceso
          funcionando();
        }
        else {
          // detener el cronometro
          document.getElementById('edit-start').innerHTML = "Empezar";

          document.getElementById('edit-timer').value = total_minutes;
          clearTimeout(timeout);
          timeout = 0;
          $('.form-item-timer').fadeIn();
          $('#edit-start').removeClass('btn-warning');
        }
      }

      function funcionando() {
        // obteneos la fecha actual
        var actual = new Date().getTime();

        // obtenemos la diferencia entre la fecha actual y la de inicio
        var diff = new Date(actual - inicio);

        // mostramos la diferencia entre la fecha actual y la inicial
        var result = LeadingZero(diff.getUTCHours()) + ":" + LeadingZero(diff.getUTCMinutes()) + ":" + LeadingZero(diff.getUTCSeconds());
        document.getElementById('crono').innerHTML = result;


        var date_diff = actual - inicio;

        var secs_from_inicio_to_actual = date_diff / 1000;
        var secs_between_dates = parseInt(Math.abs(secs_from_inicio_to_actual));

        total_minutes = Math.floor(secs_between_dates / 60);

        // Indicamos que se ejecute esta función nuevamente dentro de 1 segundo
        timeout = setTimeout(function () {
          funcionando()
        }, 1000);
      }

      /* Funcion que pone un 0 delante de un valor si es necesario */
      function LeadingZero(Time) {
        return (Time < 10) ? "0" + Time : +Time;
      }

    }
  }

  Drupal.behaviors.delete_actuacion = {
    attach: function (context, settings) {
      $('.delete-actuacion').on( "click", function() {
        if (confirm('Atención: esto eliminará la actuacion, estás seguro?')) {
          $(this).parent('.carbray-edit-actuacion').submit();
          //document.getElementById('delete-form').submit();
        } else {
          return false;
        }
      });
    }
  }

})(jQuery, Drupal, drupalSettings);