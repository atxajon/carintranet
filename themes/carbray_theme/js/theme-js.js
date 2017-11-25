(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.mybehavior = {
    attach: function (context, settings) {
      $('[data-toggle=offcanvas]').click(function() {
        $('.row-offcanvas').toggleClass('active');
      });

      var colSets = $(".row.equal");
      $(colSets).each(function () {
        var maxheight = 0;
        $(this).find('.panel-body').each(function () {
          maxheight = ($(this).outerHeight() > maxheight ? $(this).outerHeight() : maxheight);
        });
        // apply the height to all cols in this set
        $(this).find('.col-sm-4 > .panel > .panel-body').height(maxheight);
        // reset the maxheight to make it ready for next colSet iteration
        maxheight = 0;
      });

    }
  };

})(jQuery, Drupal, drupalSettings);