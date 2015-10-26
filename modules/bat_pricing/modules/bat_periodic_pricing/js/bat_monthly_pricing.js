(function ($) {

Drupal.behaviors.bat_availability = {
  attach: function(context) {

    unit_id = Drupal.settings.batPricing.batID;

    // Current month is whatever comes through -1 since js counts months starting
    // from 0
    currentMonth = parseInt(Drupal.settings.batCalendar.currentMonth - 1);
    currentYear = parseInt(Drupal.settings.batCalendar.currentYear);
    firstDay = Drupal.settings.batCalendar.firstDay;

    events = [];
    var url = Drupal.settings.basePath + '?q=bat/units/unit/' + unit_id + '/monthly-pricing/json/' + (currentYear - 2) + '/' + (currentYear + 5);
    $.ajax({
      url: url,
      dataType: 'json',
      success: function(data) {
        events = data;

        $('#calendar').fullCalendar('refetchEvents');
      }
    });


    phpmonth = currentMonth+1;
    $('#calendar').once().fullCalendar({
      schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
      contentHeight: 63,
      views: {
        timeline12Month: {
          type: 'timeline',
          slotDuration: { months: 1 },
          duration: { months: 12 }
        }
      },
      defaultView: 'timeline12Month',
      dayNamesShort:[Drupal.t("Sun"), Drupal.t("Mon"), Drupal.t("Tue"), Drupal.t("Wed"), Drupal.t("Thu"), Drupal.t("Fri"), Drupal.t("Sat")],
      monthNames:[Drupal.t("January"), Drupal.t("February"), Drupal.t("March"), Drupal.t("April"), Drupal.t("May"), Drupal.t("June"), Drupal.t("July"), Drupal.t("August"), Drupal.t("September"), Drupal.t("October"), Drupal.t("November"), Drupal.t("December")],
      firstDay: firstDay,
      header:{
        left: 'today prev,next',
        center: 'title',
        right: ''
      },
      defaultDate: moment([currentYear,phpmonth-1]),
      events: function(start, end, timezone, callback) {
        callback(events);
      },
      // Remove Time from events
      eventRender: function(event, el, view) {
        el.find('.fc-time').remove();
      },
      viewRender: function(view, element) {
        if (view.start.year() == currentYear - 2) {
          $(".fc-prev-button").prop('disabled', true); 
          $(".fc-prev-button").addClass('fc-state-disabled'); 
        }
        else {
          $(".fc-prev-button").removeClass('fc-state-disabled'); 
          $(".fc-prev-button").prop('disabled', false); 
        }

        if (view.start.year() == currentYear + 5) {
          $(".fc-next-button").prop('disabled', true); 
          $(".fc-next-button").addClass('fc-state-disabled'); 
        }
        else {
          $(".fc-next-button").removeClass('fc-state-disabled'); 
          $(".fc-next-button").prop('disabled', false); 
        }
      }
    });


    // Resize takes care of some quirks on occasion
    $(window).resize();
  }
};
})(jQuery);
