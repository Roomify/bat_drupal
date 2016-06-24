(function ($) {

/**
 * Default settings for all BAT datepickers that come in pairs.
 */
Drupal.behaviors.bat_datepicker = {
  attach: function(context) {

    $('.bat_start_date').on('change', function () {
      if ($(this).val()) {
        $(this).closest('.bat-date-range').find('.bat_end_date').attr('min', $(this).val());
      }
      else {
        $(this).closest('.bat-date-range').find('.bat_end_date').attr('min', $('.bat_end_date').attr('bat-min'));
      }
    });

  }
};

})(jQuery);
