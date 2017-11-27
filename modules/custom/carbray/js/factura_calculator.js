(function ($, Drupal) {
  Drupal.behaviors.factura_calculator = {
    attach: function (context, settings) {
      // Fire calculation on precio input blur event.
      $('#edit-coste-fieldset-precio').on('blur', function () {
        var precio = parseFloat($(this).val());
        var iva = $('#new-factura input[type=radio]:checked').val();
        var total = 0;
        if (iva == 0) {
          total = precio;
        }
        else {
          total = precio + precio * 0.21;
        }
        $('#edit-coste-fieldset-importe-total').val(total.toFixed(2));
      });

      // Fire calculation on iva radio button select change.
      $('#new-factura input[type=radio]').change(function () {
        var precio = parseFloat($('#edit-coste-fieldset-precio').val());
        var iva = $('#new-factura input[type=radio]:checked').val();
        var total = 0;
        if (iva == 0) {
          total = precio;
        }
        else {
          total = precio + precio * 0.21;
        }
        $('#edit-coste-fieldset-importe-total').val(total.toFixed(2));
      });
    }
  }
})(jQuery, Drupal);