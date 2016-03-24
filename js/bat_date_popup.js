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

            // Parse start date using the datepicker format.
            date = $.datepicker.parseDate(format, startDate);

            if (instance.settings.endDateDays !== undefined) {
              date.setDate(date.getDate() + instance.settings.endDateDays);
            }

            // If this event type is of daily granularity, ensure that the end date
            // must be at least one day greater than the start date.
            if (Drupal.settings.bat.batDateGranularity == 'bat_daily') {
              date = new Date(date.valueOf()+864E5);
            }
            $(this).datepicker("option", "minDate", date);
            $(this).datepicker("option", "maxDate", Drupal.settings.datePopup[this.id].settings.maxDate);
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
