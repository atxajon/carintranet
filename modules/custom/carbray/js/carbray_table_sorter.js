(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.carbray_table_sorter = {
    attach: function (context, settings) {
      // Prevent js code from double firing.
      if (context == document) {
        $(".tablesorter").tablesorter({
          sortInitialOrder: "desc"
        });
      }
    }
  }
})(jQuery, Drupal, drupalSettings);