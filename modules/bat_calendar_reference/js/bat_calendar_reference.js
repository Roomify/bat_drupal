(function ($) {

  Drupal.behaviors.bat_calendar_reference = {
    attach: function(context) {
      var today = moment();

      var views = 'timelineDay, timelineTenDay, timelineMonth, timelineYear';
      var defaultView = 'timelineMonth';

      businessHours = {
        start: '00:00',
        end: '24:00',
        dow: [0, 1, 2, 3, 4, 5, 6],
      };

      $('.cal').once('cal', function() {
        var lastSource;
        var cal_id = $(this).siblings('.calendar-title').attr('id');

        if (Drupal.settings.batCalendarReference[cal_id].eventGranularity == 'bat_daily') {
          views = 'timelineMonth, timelineYear';
          defaultView = 'timelineMonth';
        }
        else if (Drupal.settings.batCalendarReference[cal_id].eventGranularity == 'bat_hourly') {
          views = 'timelineDay, timelineTenDay, timelineMonth';
          defaultView = 'timelineDay';
        }

        $(this).fullCalendar({
          schedulerLicenseKey: Drupal.settings.batCalendarReference[cal_id].schedulerLicenseKey,
          editable: false,
          selectable: false,
          dayNamesShort:[Drupal.t("Sun"), Drupal.t("Mon"), Drupal.t("Tue"), Drupal.t("Wed"), Drupal.t("Thu"), Drupal.t("Fri"), Drupal.t("Sat")],
          monthNames:[Drupal.t("January"), Drupal.t("February"), Drupal.t("March"), Drupal.t("April"), Drupal.t("May"), Drupal.t("June"), Drupal.t("July"), Drupal.t("August"), Drupal.t("September"), Drupal.t("October"), Drupal.t("November"), Drupal.t("December")],
          header: {
            left: 'today, prev, next',
            center: 'title',
            right: views,
          },
          businessHours: businessHours,
          defaultView: defaultView,
          views: {
            timelineDay: {
              buttonText: ':15 slots',
              slotDuration: '00:15'
            },
            timelineTenDay: {
              type: 'timeline',
              duration: { days: 10 }
            }
          },
          resourceAreaWidth: '25%',
          resourceLabelText: 'Rooms',
          resources: '/bat/v2/units-calendar?types=' + Drupal.settings.batCalendarReference[cal_id].unitTypes + '&ids=' + Drupal.settings.batCalendarReference[cal_id].unitIDs,
          events: '/bat/v2/events-calendar?unit_types=' + Drupal.settings.batCalendarReference[cal_id].unitTypes + '&event_types=' + Drupal.settings.batCalendarReference[cal_id].eventType + '&unit_ids=' + Drupal.settings.batCalendarReference[cal_id].unitIDs,
          windowResize: function(view) {
            $(this).fullCalendar('refetchEvents');
          },
          eventRender: function(event, el, view) {
            // Remove Time from events.
            el.find('.fc-time').remove();

            // Append event title when rendering as background.
            if (event.rendering == 'background' && event.fixed == 0) {
              el.append('<span class="fc-title">' + (event.title || '&nbsp;') + '</span>');
            }
          }
        });

      });

      // Resize takes care of some quirks on occasion
      $(window).resize();

    }
  };

})(jQuery);
