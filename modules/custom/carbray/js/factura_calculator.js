(function ($, Drupal) {
  Drupal.behaviors.factura_calculator = {
    attach: function (context, settings) {
      // Fire calculation on precio input blur event.
      $('.field--name-field-factura-servicios .form-number', context).once('factura_calculator').on('blur', function () {
        var precio = parseFloat($(this).val());
        var acc_total = 0;
        var total = 0;

        $('.field--name-field-factura-servicios .form-number').each(function( index ) {
          acc_total += parseFloat($(this).val());
        });

        if ($('#edit-field-factura-iva-value').is(':checked')) {
          total = acc_total + acc_total * 0.21;
        }
        else {
          total = acc_total;
        }

        $('#edit-field-factura-precio-0-value').val(total.toFixed(2));
      });

      // Fire calculation on iva radio button select change.
      $('#edit-field-factura-iva-value', context).once('factura_calculator').on('change', function () {
        var servicios_total = 0;
        var prov_fondos = 0;

          $('.field--name-field-factura-servicios .form-number').each(function( index ) {
          servicios_total += parseFloat($(this).val());
        });
        var total = 0;

        if ($('#edit-field-factura-iva-value').is(':checked')) {
          total = servicios_total + servicios_total * 0.21;
          prov_fondos = parseFloat($('#edit-field-factura-provision-de-fondo-0-value').val());
          // If var is undefined (NaN).
          if (prov_fondos !== prov_fondos) {
            prov_fondos = 0;
          }
          total += prov_fondos;
        }
        else {
          total = servicios_total;
          prov_fondos = parseFloat($('#edit-field-factura-provision-de-fondo-0-value').val());
          // If var is undefined (NaN).
          if (prov_fondos !== prov_fondos) {
            prov_fondos = 0;
          }
          total += prov_fondos;
        }

        $('#edit-field-factura-precio-0-value').val(total.toFixed(2));
      });

      // Fire calculation on provision de fondos input blur event.
      $('#edit-field-factura-provision-de-fondo-0-value', context).once('factura_calculator').on('blur', function () {
        var servicios_total = 0;
        $('.field--name-field-factura-servicios .form-number').each(function( index ) {
          servicios_total += parseFloat($(this).val());
        });

        var prov_fondos = parseFloat($(this).val());
        var total = servicios_total + prov_fondos;

        $('#edit-field-factura-precio-0-value').val(total.toFixed(2));
      });


      /**
       * Old code for NewFacturaFOrm.php, currently unused
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