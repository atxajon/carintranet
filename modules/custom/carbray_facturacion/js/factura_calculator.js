(function ($, Drupal) {
  Drupal.behaviors.factura_calculator = {
    attach: function (context, settings) {
      // Fire calculation on precio input blur event.
      $('.field--name-field-factura-servicios .form-number', context).once('factura_calculator').on('blur', function () {
        do_calculation();
      });

      // Fire calculation on iva radio button select change.
      $('#edit-field-factura-iva-value', context).once('factura_calculator').on('change', function () {
        do_calculation();
      });

      // Fire calculation on provision de fondos input blur event.
      $('#edit-field-factura-provision-de-fondo-0-value', context).once('factura_calculator').on('blur', function () {
        do_calculation();
      });

      function do_calculation() {
        var servicios_total = 0;
        var prov_fondos = 0;
        var total = 0;

        $('.field--name-field-factura-servicios .form-number').each(function( index ) {
          var this_servicio = parseFloat($(this).val());
          // If var is undefined (NaN).
          if (this_servicio !== this_servicio) {
            this_servicio = 0;
          }
          servicios_total += this_servicio;
        });

        if ($('#edit-field-factura-iva-value').is(':checked')) {
          total = servicios_total + servicios_total * 0.21;
        }
        else {
          total = servicios_total;
        }

        prov_fondos = parseFloat($('#edit-field-factura-provision-de-fondo-0-value').val());
        // If var is undefined (NaN).
        if (prov_fondos !== prov_fondos) {
          prov_fondos = 0;
        }
        total += prov_fondos;
        total = total.toFixed(2);
        // total += 'what';

        $('#edit-field-factura-precio-0-value').val(total);
      }

      // Prevent from accidentaly typing 'enter' key on servicios adding,
      // which toggles some undesired show row weight column...
      $('#node-factura-form').keypress(function(event) {
        if (event.keyCode == '13') {
          event.preventDefault();
        }
      });


      /**
       * Old code for NewFacturaForm.php, currently unused
       * as we are favoring using node/add/factura default form.
       */
      //$('#edit-coste-fieldset-precio').on('blur', function () {
      //  var precio = parseFloat($(this).val());
      //  var iva = $('#new-factura input[type=radio]:checked').val();
      //  var total = 0;
      //  if (iva == 0) {
      //    total = precio;
      //  }
      //  else {
      //    total = precio + precio * 0.21;
      //  }
      //  $('#edit-coste-fieldset-importe-total').val(total.toFixed(2));
      //});

      // Fire calculation on iva radio button select change.
      //$('#new-factura input[type=radio]').change(function () {
      //  var precio = parseFloat($('#edit-coste-fieldset-precio').val());
      //  var iva = $('#new-factura input[type=radio]:checked').val();
      //  var total = 0;
      //  if (iva == 0) {
      //    total = precio;
      //  }
      //  else {
      //    total = precio + precio * 0.21;
      //  }
      //  $('#edit-coste-fieldset-importe-total').val(total.toFixed(2));
      //});
    }
  }
})(jQuery, Drupal);