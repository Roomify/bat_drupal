(function ($) {

Drupal.behaviors.batAvailabilityPrepareForm = {
  attach: function(context) {
    $("form#bat-filter-month-form select").once('select').change(function() {
      $("form#bat-filter-month-form").submit();
    });

    $('#edit-select-all').once('select').change(function() {
      var table = $(this).closest('table')[0];
      if (this.options.selectedIndex == 1) {
        $('input[id^="edit-bat"]:not(:disabled)', table).attr('checked', true);
      }
      else if (this.options.selectedIndex == 2) {
        $('input[id^="edit-bat"]:not(:disabled)', table).attr('checked', true);
      }
      else if (this.options.selectedIndex == 3) {
        $('input[id^="edit-bat"]:not(:disabled)', table).attr('checked', false);
      }
    });
  }
};

Drupal.behaviors.batPricing = {
  attach: function(context) {

    $('#edit-start-week, #edit-end-week').datepicker({
      showWeek: true,
      firstDay: 1,
      onSelect: function(dateText, inst) {
        var date = new Date(dateText);
        var date_year = date.getFullYear();
        var week_number = $.datepicker.iso8601Week(date);
        if (date.getMonth() == 11 && week_number == 1) {
          date_year = date_year + 1;
        }
        $(this).val('Year ' + date_year + ' - Week ' + week_number);
      },
      beforeShowDay: function(date) {
        var day = date.getDay();
        return [(day == 1), ''];
      }
    });

    // Current month is whatever comes through -1 since js counts months starting from 0
    currentMonth = parseInt(Drupal.settings.batUnitManagement.currentMonth - 1);
    currentYear = parseInt(Drupal.settings.batUnitManagement.currentYear);

    var calendars = [];
    var i = 0;
    for (i=0; i<Drupal.settings.batUnitManagement.batNumber; i++) {
      calendars[i] = new Array('#calendar' + i, currentMonth, currentYear);
    }

    var c = 0;
    $.each(calendars, function(key, value) {
      phpmonth = value[1]+1;

      var unit_id = Drupal.settings.batUnitManagement.batId[c];

      $(value[0]).once().fullCalendar({
        schedulerLicenseKey: Drupal.settings.batCalendar.schedulerLicenseKey,
        contentHeight: 74,
        views: {
          timeline8Week: {
            type: 'timeline',
            slotDuration: { days: 7 },
            duration: { days: 64 }
          }
        },
        defaultView: 'timeline8Week',
        dayNamesShort:[Drupal.t("Sun"), Drupal.t("Mon"), Drupal.t("Tue"), Drupal.t("Wed"), Drupal.t("Thu"), Drupal.t("Fri"), Drupal.t("Sat")],
        monthNames:[Drupal.t("January"), Drupal.t("February"), Drupal.t("March"), Drupal.t("April"), Drupal.t("May"), Drupal.t("June"), Drupal.t("July"), Drupal.t("August"), Drupal.t("September"), Drupal.t("October"), Drupal.t("November"), Drupal.t("December")],
        defaultDate: moment([value[2],value[1]]),
        header:{
          left: '',
          center: '',
          right: ''
        },
        events: function(start, end, timezone, callback) {
          var url = Drupal.settings.basePath + '?q=bat/units/unit/' + Drupal.settings.batUnitManagement.batId[c] + '/weekly-pricing/json/' + (currentYear - 1) + '/' + (currentYear + 1);
          $.ajax({
            url: url,
            dataType: 'json',
            success: function(data) {
              callback(data);
            }
          });
        },
        // Remove Time from events
        eventRender: function(event, el, view) {
          el.find('.fc-time').remove();
        },
      });

      c++;
    });

    // Resize takes care of some quirks on occasion
    $(window).resize();

  }
};
})(jQuery);
