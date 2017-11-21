(function ($, Drupal) {
  Drupal.behaviors.factura_calculator = {
    attach: function (context, settings) {
      // Fire calculation on precio input blur event.
      $('#edit-precio').on('blur', function () {
        var precio = parseFloat($(this).val());
        var iva = $('input[name=iva]:checked').val();
        var total = 0;
        if (iva == 0) {
          total = precio;
        }
        else {
          total = precio + precio * 0.21;
        }
        $('#edit-importe-total').val(total.toFixed(2));
      });

      // Fire calculation on iva radio button select change.
      $('input:radio[name=iva]:radio').change(function () {
        var precio = parseFloat($('#edit-precio').val());
        var iva = $('input[name=iva]:checked').val();
        var total = 0;
        if (iva == 0) {
          total = precio;
        }
        else {
          total = precio + precio * 0.21;
        }
        $('#edit-importe-total').val(total.toFixed(2));
      });
    }
  }
})(jQuery, Drupal);