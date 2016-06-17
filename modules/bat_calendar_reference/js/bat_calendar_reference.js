(function ($) {

  Drupal.behaviors.bat_calendar_reference = {
    attach: function(context) {
      var today = moment();

      businessHours = {
        start: '00:00',
        end: '24:00',
        dow: [0, 1, 2, 3, 4, 5, 6],
      };

      $('.cal').once('cal', function() {
        var lastSource;
        var cal_id = $(this).attr('id');

        $(this).fullCalendar({
          schedulerLicenseKey: drupalSettings.batCalendar[cal_id].schedulerLicenseKey,
          editable: drupalSettings.batCalendar[cal_id].editable,
          selectable: drupalSettings.batCalendar[cal_id].selectable,
          dayNamesShort:[Drupal.t('Sun'), Drupal.t('Mon'), Drupal.t('Tue'), Drupal.t('Wed'), Drupal.t('Thu'), Drupal.t('Fri'), Drupal.t('Sat')],
          monthNames:[Drupal.t('January'), Drupal.t('February'), Drupal.t('March'), Drupal.t('April'), Drupal.t('May'), Drupal.t('June'), Drupal.t('July'), Drupal.t('August'), Drupal.t('September'), Drupal.t('October'), Drupal.t('November'), Drupal.t('December')],
          header: {
            left: drupalSettings.batCalendar[cal_id].headerLeft,
            center: drupalSettings.batCalendar[cal_id].headerCenter,
            right: drupalSettings.batCalendar[cal_id].headerRight,
          },
          businessHours: drupalSettings.batCalendar[cal_id].businessHours,
          defaultView: drupalSettings.batCalendar[cal_id].defaultView,
          selectConstraint: drupalSettings.batCalendar[cal_id].selectConstraint,
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
          customButtons: drupalSettings.batCalendar[cal_id].customButtons,
          resourceAreaWidth: drupalSettings.batCalendar[cal_id].resourceAreaWidth,
          resourceLabelText: drupalSettings.batCalendar[cal_id].resourceLabelText,
          resources: drupalSettings.basePath + '?q=bat/v2/units-calendar&types=' + drupalSettings.batCalendar[cal_id].unitTypes + '&ids=' + drupalSettings.batCalendar[cal_id].unitIDs + '&event_type=' + drupalSettings.batCalendar[cal_id].eventType,
          events: drupalSettings.basePath + '?q=bat/v2/events-calendar&unit_types=' + drupalSettings.batCalendar[cal_id].unitTypes + '&event_types=' + drupalSettings.batCalendar[cal_id].eventType + '&unit_ids=' + drupalSettings.batCalendar[cal_id].unitIDs + '&background=' + drupalSettings.batCalendar[cal_id].background,
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
