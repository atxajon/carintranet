(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.mybehavior = {
    attach: function (context, settings) {
      $('[data-toggle=offcanvas]').click(function() {
        $('.row-offcanvas').toggleClass('active');
      });
    }
  };

})(jQuery, Drupal, drupalSettings);