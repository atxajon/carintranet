(function ($, Drupal) {
  Drupal.behaviors.client_search = {
    attach: function (context, settings) {
      // Submit the search client form on autocomplete list click.
      $('html').on('click touchstart','.ui-autocomplete li a', function(e){
        $('#search-users .button').click();
      });
    }
  }
})(jQuery, Drupal);