(function ($, Drupal) {
  Drupal.behaviors.date_autosubmit = {
    attach: function (context, settings) {
      if (context == document) {
        // Submit the informe dates filter form on select list change.
        $('#edit-last-months').on('change', function () {
          $('.filter').click();
        })
      }
    }
  }
})(jQuery, Drupal);