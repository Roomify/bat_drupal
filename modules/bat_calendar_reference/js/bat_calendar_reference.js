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
          schedulerLicenseKey: Drupal.settings.batCalendar[cal_id].schedulerLicenseKey,
          themeSystem: Drupal.settings.batCalendar[cal_id].themeSystem,
          locale: Drupal.settings.batCalendar[cal_id].locale,
          slotWidth: Drupal.settings.batCalendar[cal_id].slotWidth,
          height: Drupal.settings.batCalendar[cal_id].calendarHeight,
          editable: Drupal.settings.batCalendar[cal_id].editable,
          selectable: Drupal.settings.batCalendar[cal_id].selectable,
          eventStartEditable: Drupal.settings.batCalendar[cal_id].eventStartEditable,
          eventDurationEditable: Drupal.settings.batCalendar[cal_id].eventDurationEditable,
          dayNamesShort:[Drupal.t('Sun'), Drupal.t('Mon'), Drupal.t('Tue'), Drupal.t('Wed'), Drupal.t('Thu'), Drupal.t('Fri'), Drupal.t('Sat')],
          monthNames:[Drupal.t('January'), Drupal.t('February'), Drupal.t('March'), Drupal.t('April'), Drupal.t('May'), Drupal.t('June'), Drupal.t('July'), Drupal.t('August'), Drupal.t('September'), Drupal.t('October'), Drupal.t('November'), Drupal.t('December')],
          header: {
            left: Drupal.settings.batCalendar[cal_id].headerLeft,
            center: Drupal.settings.batCalendar[cal_id].headerCenter,
            right: Drupal.settings.batCalendar[cal_id].headerRight,
          },
          allDayDefault: Drupal.settings.batCalendar[cal_id].allDayDefault,
          validRange: Drupal.settings.batCalendar[cal_id].validRange,
          businessHours: Drupal.settings.batCalendar[cal_id].businessHours,
          defaultView: Drupal.settings.batCalendar[cal_id].defaultView,
          scrollTime: Drupal.settings.batCalendar[cal_id].scrollTime,
          selectConstraint: (Drupal.settings.batCalendar[cal_id].selectConstraint == null) ? undefined : Drupal.settings.batCalendar[cal_id].selectConstraint,
          minTime: Drupal.settings.batCalendar[cal_id].minTime,
          maxTime: Drupal.settings.batCalendar[cal_id].maxTime,
          hiddenDays: Drupal.settings.batCalendar[cal_id].hiddenDays,
          validRange: Drupal.settings.batCalendar[cal_id].validRange,
          defaultDate: $.fullCalendar.moment(Drupal.settings.batCalendar[cal_id].defaultDate),
          views: {
            timelineDay: {
              buttonText: Drupal.settings.batCalendar[cal_id].viewsTimelineDayButtonText,
              slotDuration: Drupal.settings.batCalendar[cal_id].viewsTimelineDaySlotDuration,
              slotLabelFormat: Drupal.settings.batCalendar[cal_id].viewsTimelineDaySlotLabelFormat,
              titleFormat: Drupal.settings.batCalendar[cal_id].viewsTimelineDayTitleFormat
            },
            timelineSevenDay: {
              buttonText: Drupal.settings.batCalendar[cal_id].viewsTimelineSevenDayButtonText,
              duration: Drupal.settings.batCalendar[cal_id].viewsTimelineSevenDayDuration,
              slotDuration: Drupal.settings.batCalendar[cal_id].viewsTimelineSevenDaySlotDuration,
              slotLabelFormat: Drupal.settings.batCalendar[cal_id].viewsTimelineSevenDaySlotLabelFormat,
              titleFormat: Drupal.settings.batCalendar[cal_id].viewsTimelineSevenDayTitleFormat,
              type: 'timeline'
            },
            timelineTenDay: {
              buttonText: Drupal.settings.batCalendar[cal_id].viewsTimelineTenDayButtonText,
              duration: Drupal.settings.batCalendar[cal_id].viewsTimelineTenDayDuration,
              slotDuration: Drupal.settings.batCalendar[cal_id].viewsTimelineTenDaySlotDuration,
              slotLabelFormat: Drupal.settings.batCalendar[cal_id].viewsTimelineTenDaySlotLabelFormat,
              titleFormat: Drupal.settings.batCalendar[cal_id].viewsTimelineTenDayTitleFormat,
              type: 'timeline'
            },
            timelineThirtyDay: {
              buttonText: Drupal.settings.batCalendar[cal_id].viewsTimelineThirtyDayButtonText,
              duration: Drupal.settings.batCalendar[cal_id].viewsTimelineThirtyDayDuration,
              slotDuration: Drupal.settings.batCalendar[cal_id].viewsTimelineThirtyDaySlotDuration,
              slotLabelFormat: Drupal.settings.batCalendar[cal_id].viewsTimelineThirtyDaySlotLabelFormat,
              titleFormat: Drupal.settings.batCalendar[cal_id].viewsTimelineThirtyDayTitleFormat,
              type: 'timeline'
            },
            timeline365Day: {
              buttonText: Drupal.settings.batCalendar[cal_id].viewsTimeline365DayButtonText,
              duration: Drupal.settings.batCalendar[cal_id].viewsTimeline365DayDuration,
              slotLabelFormat: Drupal.settings.batCalendar[cal_id].viewsTimeline365DaySlotLabelFormat,
              titleFormat: Drupal.settings.batCalendar[cal_id].viewsTimeline365DayTitleFormat,
              type: 'timeline'
            }
          },
          groupByResource: Drupal.settings.batCalendar[cal_id].groupByResource,
          groupByDateAndResource: Drupal.settings.batCalendar[cal_id].groupByDateAndResource,
          firstDay: Drupal.settings.batCalendar[cal_id].firstDay,
          eventOrder: Drupal.settings.batCalendar[cal_id].eventOrder,
          titleFormat: Drupal.settings.batCalendar[cal_id].titleFormat,
          slotLabelFormat: Drupal.settings.batCalendar[cal_id].slotLabelFormat,
          resourceAreaWidth: Drupal.settings.batCalendar[cal_id].resourceAreaWidth,
          resourceLabelText: Drupal.settings.batCalendar[cal_id].resourceLabelText,
          resources: Drupal.settings.basePath + '?q=' + Drupal.settings.pathPrefix + 'bat/v2/units-calendar&types=' + Drupal.settings.batCalendar[cal_id].unitTypes + '&ids=' + Drupal.settings.batCalendar[cal_id].unitIDs + '&event_type=' + Drupal.settings.batCalendar[cal_id].eventType,
          events: Drupal.settings.basePath + '?q=' + Drupal.settings.pathPrefix + 'bat/v2/events-calendar&unit_types=' + Drupal.settings.batCalendar[cal_id].unitTypes + '&event_types=' + Drupal.settings.batCalendar[cal_id].eventType + '&unit_ids=' + Drupal.settings.batCalendar[cal_id].unitIDs + '&background=' + Drupal.settings.batCalendar[cal_id].background,
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
