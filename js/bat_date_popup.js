(function ($) {

/*
 * Default settings for all BAT datepickers that come in pairs.
 */
Drupal.behaviors.bat_datepicker = {
  attach: function(context) {

    $.datepicker.setDefaults({
      beforeShow: function() {

        // Prevent a negative date range from being selected by updating the
        // end date popup's minDate to the specified start date.
        instance = $(this).data("datepicker");
        if (instance.settings.startDateSelector !== undefined) {
          startDate = $(instance.settings.startDateSelector).val();
          format = instance.settings.dateFormat || $.datepicker._defaults.dateFormat;
          if (startDate !== undefined && startDate !== '') {
            date = $.datepicker.parseDate(format, startDate, instance.settings);
            $(this).datepicker("option", "minDate", date);
          }
        }

        // If you think this is ugly, you are right - read this though:
        // http://blog.foersom.dk/post/598839422/dealing-with-z-index-in-jquery-uis-datepicker
        setTimeout(function() {
          $(".ui-datepicker").css("z-index", 12);
        }, 10);
      }
    });
  }
};
})(jQuery);
