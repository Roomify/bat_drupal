(function ($) {

// Define our objects.
Drupal.batCalendar = Drupal.batCalendar || {};
Drupal.batCalendar.Modal = Drupal.batCalendar.Modal || {};

Drupal.behaviors.bat_event = {
  attach: function(context) {

    var calendars = [];
    for (id in Drupal.settings.batCalendar) {
      calendars[id] = new Array('#' + Drupal.settings.batCalendar[id]['id']);
    }

    // Refresh the event once the modal is closed.
    $(document).one('CToolsDetachBehaviors', function() {
      $.each(calendars, function(key, value) {
        $(value[0]).fullCalendar('refetchEvents');
      });
    });

    $.each(calendars, function(key, value) {

      $(value[0]).once().fullCalendar({
        schedulerLicenseKey: Drupal.settings.batCalendar[key].schedulerLicenseKey,
        themeSystem: Drupal.settings.batCalendar[key].themeSystem,
        locale: Drupal.settings.batCalendar[key].locale,
        slotWidth: Drupal.settings.batCalendar[key].slotWidth,
        height: Drupal.settings.batCalendar[key].calendarHeight,
        editable: Drupal.settings.batCalendar[key].editable,
        selectable: Drupal.settings.batCalendar[key].selectable,
        displayEventTime: false,
        eventStartEditable: Drupal.settings.batCalendar[key].eventStartEditable,
        eventDurationEditable: Drupal.settings.batCalendar[key].eventDurationEditable,
        dayNamesShort:[Drupal.t('Sun'), Drupal.t('Mon'), Drupal.t('Tue'), Drupal.t('Wed'), Drupal.t('Thu'), Drupal.t('Fri'), Drupal.t('Sat')],
        monthNames:[Drupal.t('January'), Drupal.t('February'), Drupal.t('March'), Drupal.t('April'), Drupal.t('May'), Drupal.t('June'), Drupal.t('July'), Drupal.t('August'), Drupal.t('September'), Drupal.t('October'), Drupal.t('November'), Drupal.t('December')],
        header: {
          left: Drupal.settings.batCalendar[key].headerLeft,
          center: Drupal.settings.batCalendar[key].headerCenter,
          right: Drupal.settings.batCalendar[key].headerRight,
        },
        allDayDefault: Drupal.settings.batCalendar[key].allDayDefault,
        businessHours: Drupal.settings.batCalendar[key].businessHours,
        defaultView: Drupal.settings.batCalendar[key].defaultView,
        scrollTime: Drupal.settings.batCalendar[key].scrollTime,
        selectConstraint: (Drupal.settings.batCalendar[key].selectConstraint == null) ? undefined : Drupal.settings.batCalendar[key].selectConstraint,
        minTime: Drupal.settings.batCalendar[key].minTime,
        maxTime: Drupal.settings.batCalendar[key].maxTime,
        hiddenDays: Drupal.settings.batCalendar[key].hiddenDays,
        validRange: Drupal.settings.batCalendar[key].validRange,
        defaultDate: $.fullCalendar.moment(Drupal.settings.batCalendar[key].defaultDate),
        views: {
          timelineDay: {
            buttonText: Drupal.settings.batCalendar[key].viewsTimelineDayButtonText,
            slotDuration: Drupal.settings.batCalendar[key].viewsTimelineDaySlotDuration,
            slotLabelFormat: Drupal.settings.batCalendar[key].viewsTimelineDaySlotLabelFormat,
            titleFormat: Drupal.settings.batCalendar[key].viewsTimelineDayTitleFormat
          },
          timelineSevenDay: {
            buttonText: Drupal.settings.batCalendar[key].viewsTimelineSevenDayButtonText,
            duration: Drupal.settings.batCalendar[key].viewsTimelineSevenDayDuration,
            slotDuration: Drupal.settings.batCalendar[key].viewsTimelineSevenDaySlotDuration,
            slotLabelFormat: Drupal.settings.batCalendar[key].viewsTimelineSevenDaySlotLabelFormat,
            titleFormat: Drupal.settings.batCalendar[key].viewsTimelineSevenDayTitleFormat,
            type: 'timeline'
          },
          timelineTenDay: {
            buttonText: Drupal.settings.batCalendar[key].viewsTimelineTenDayButtonText,
            duration: Drupal.settings.batCalendar[key].viewsTimelineTenDayDuration,
            slotDuration: Drupal.settings.batCalendar[key].viewsTimelineTenDaySlotDuration,
            slotLabelFormat: Drupal.settings.batCalendar[key].viewsTimelineTenDaySlotLabelFormat,
            titleFormat: Drupal.settings.batCalendar[key].viewsTimelineTenDayTitleFormat,
            type: 'timeline'
          },
          timelineThirtyDay: {
            buttonText: Drupal.settings.batCalendar[key].viewsTimelineThirtyDayButtonText,
            duration: Drupal.settings.batCalendar[key].viewsTimelineThirtyDayDuration,
            slotDuration: Drupal.settings.batCalendar[key].viewsTimelineThirtyDaySlotDuration,
            slotLabelFormat: Drupal.settings.batCalendar[key].viewsTimelineThirtyDaySlotLabelFormat,
            titleFormat: Drupal.settings.batCalendar[key].viewsTimelineThirtyDayTitleFormat,
            type: 'timeline'
          },
          timeline365Day: {
            buttonText: Drupal.settings.batCalendar[key].viewsTimeline365DayButtonText,
            duration: Drupal.settings.batCalendar[key].viewsTimeline365DayDuration,
            slotLabelFormat: Drupal.settings.batCalendar[key].viewsTimeline365DaySlotLabelFormat,
            titleFormat: Drupal.settings.batCalendar[key].viewsTimeline365DayTitleFormat,
            type: 'timeline'
          },
          agendaOneDay: {
            buttonText: Drupal.settings.batCalendar[key].viewsAgendaOneDayButtonText,
            duration: Drupal.settings.batCalendar[key].viewsAgendaOneDayDuration,
            type: 'agenda'
          },
          agendaSevenDay: {
            buttonText: Drupal.settings.batCalendar[key].viewsAgendaSevenDayButtonText,
            duration: Drupal.settings.batCalendar[key].viewsAgendaSevenDayDuration,
            type: 'agenda'
          },
          agenda: {
            buttonText: Drupal.settings.batCalendar[key].viewsAgendaButtonText
          },
          week: {
            buttonText: Drupal.settings.batCalendar[key].viewsWeekButtonText
          },
          day: {
            buttonText: Drupal.settings.batCalendar[key].viewsDayButtonText
          }
        },
        groupByResource: Drupal.settings.batCalendar[key].groupByResource,
        groupByDateAndResource: Drupal.settings.batCalendar[key].groupByDateAndResource,
        allDaySlot: Drupal.settings.batCalendar[key].allDaySlot,
        firstDay: Drupal.settings.batCalendar[key].firstDay,
        defaultTimedEventDuration: Drupal.settings.batCalendar[key].defaultTimedEventDuration,
        customButtons: $.extend(Drupal.settings.batCalendar[key].customButtons, { datepickerinline: { text: ' ' }, datepicker: { text: Drupal.t('Go to Date'), click: datepicker } }),
        eventOrder: Drupal.settings.batCalendar[key].eventOrder,
        titleFormat: Drupal.settings.batCalendar[key].titleFormat,
        slotLabelFormat: Drupal.settings.batCalendar[key].slotLabelFormat,
        resourceAreaWidth: Drupal.settings.batCalendar[key].resourceAreaWidth,
        resourceLabelText: Drupal.settings.batCalendar[key].resourceLabelText,
        resources: function(callback) {
          $.ajax({
            url: Drupal.settings.basePath + '?q=' + Drupal.settings.pathPrefix + 'bat/v2/units-calendar&types=' + Drupal.settings.batCalendar[key].unitType + '&event_type=' + Drupal.settings.batCalendar[key].eventType + '&grouping_entity_type=' + Drupal.settings.batCalendar[key].grouping_entity_type + '&grouping_ids=' + Drupal.settings.batCalendar[key].grouping_ids + '&collapse_childrens=' + Drupal.settings.batCalendar[key].collapse_childrens
          })
          .done(function(resources) {
            if (Drupal.settings.batCalendar[key].hideResourceTypes) {
              resources = $.map(resources, function(e, i) {
                return e.children;
              });
            }

            callback(resources);
          });
        },
        selectOverlap: function(event) {
          // Allow selections over background events, but not any other types of events.
          return event.rendering === 'background';
        },
        events: Drupal.settings.basePath + '?q=' + Drupal.settings.pathPrefix + 'bat/v2/events-calendar&unit_types=' + Drupal.settings.batCalendar[key].unitType + '&event_types=' + Drupal.settings.batCalendar[key].eventType,
        windowResize: function(view) {
          $(this).fullCalendar('refetchEvents');
        },
        eventClick: function(event, jsEvent, view) {
          if (event.editable && Drupal.settings.batCalendar[key].enableModal) {
            var unit_id = event.resourceId.substring(1);
            var sd = event.start.format('YYYY-MM-DD HH:mm');
            event.end.add(1, 'm');
            var ed = event.end.format('YYYY-MM-DD HH:mm');

            // Open the modal for edit
            Drupal.batCalendar.Modal(view, key, event.bat_id, sd, ed, unit_id);
          }
        },
        select: function(start, end, jsEvent, view, resource) {
          if (resource.create_event && Drupal.settings.batCalendar[key].enableModal) {
            var unit_id = resource.id.substring(1);

            var ed = end.format('YYYY-MM-DD HH:mm');
            var sd = start.format('YYYY-MM-DD HH:mm');

            // Open the modal for edit
            Drupal.batCalendar.Modal(this, key, 0, sd, ed, unit_id);
          }

          $(value[0]).fullCalendar('unselect');
        },
        selectAllow: function(selectInfo) {
          if (Drupal.settings.batCalendar[key].selectAllowBusinessHours) {
            var business_hours = $(value[0]).fullCalendar('option', 'businessHours');

            var start_day = selectInfo.start.day();
            var end_day = selectInfo.end.clone().subtract(1, 'minute').day();

            if (start_day == end_day) {
              if (isInsideBusinessHour(business_hours, start_day, selectInfo.start.format('HH:mm'), selectInfo.end.format('HH:mm'))) {
                return true;
              }
            }
            else {
              if (!isInsideBusinessHour(business_hours, start_day, selectInfo.start.format('HH:mm'), '24:00')) {
                return false;
              }

              for (date of enumerateDaysBetweenDates(selectInfo.start, selectInfo.end)) {
                if (!isInsideBusinessHour(business_hours, date.day(), '00:00', '24:00')) {
                  return false;
                }
              }

              if (!isInsideBusinessHour(business_hours, end_day, '00:00', selectInfo.end.format('HH:mm'))) {
                return false;
              }

              return true;
            }

            return false;
          }
        },
        eventOverlap: function(stillEvent, movingEvent) {
          // Prevent events from being drug over blocking events.
          return !stillEvent.blocking && (stillEvent.type == movingEvent.type);
        },
        eventDrop: function(event, delta, revertFunc) {
          if (event.editable) {
            // Prevent events from being dropped over unit types row.
            if (event.resourceId.match(/^S[0-9]+$/)) {
              saveBatEvent(event, revertFunc, calendars, key);
            }
            else {
              revertFunc();
            }
          }
          else {
            revertFunc();
          }
        },
        eventResize: function(event, delta, revertFunc) {
          if (event.editable) {
            saveBatEvent(event, revertFunc, calendars, key);
          }
          else {
            revertFunc();
          }
        },
        viewRender: function(view, element) {
          var calendar = $(element).parent().parent();

          $(calendar).find('button.fc-datepickerinline-button').replaceWith('<div class="inline-datepicker"></div>')

          $(calendar).find('.inline-datepicker').datepicker({
            dateFormat: 'mm/dd/yy',
            onSelect: function(date) {
              $(calendar).fullCalendar('gotoDate', date);
            },
            onClose: function(date) {
              $(this).remove();
            }
          })
          .datepicker('setDate', $(calendar).fullCalendar('getDate').format('MM/DD/YYYY'));
        },
        eventAfterRender: function(event, element, view) {
          // Append event title when rendering as background.
          if (event.rendering == 'background') {
            if (event.fixed == 0) {
              if ((view.type == 'timelineThirtyDay' || view.type == 'timelineMonth' || view.type == 'timelineYear' || view.type == 'timeline365Day') && Drupal.settings.batCalendar[key].repeatEventTitle) {
                var start = event.start.clone();
                start.subtract(start.hour(), 'hours').subtract(start.minute(), 'minutes');

                if (start < view.start) {
                  start = view.start.clone();
                }

                if (event.end === null) {
                  var end = event.start.clone();
                }
                else {
                  var end = event.end.clone();

                  if (end > view.end) {
                    end = view.end.clone();
                  }
                  else if (end.unix() != view.end.unix()) {
                    end.add(1, 'minute');
                  }
                }

                var index = 0;

                // Event width.
                var width = element.width();
                // Event colspan number.
                var colspan = element.get(0).colSpan;

                if (event.end != null) {
                  // Single cell width.
                  var cell_width = width/(end.diff(start, 'days'));

                  while (start < end) {
                    element.append('<span class="fc-title" style="position:absolute; top:8px; left:' + (index * cell_width + 3) + 'px;">' + (event.title || '&nbsp;') + '</span>');
                    start = start.add(1, 'day');
                    index++;
                  }
                }
                else {
                  element.append('<span class="fc-title" style="position:absolute; top:8px; left:3px;">' + (event.title || '&nbsp;') + '</span>');
                  start = start.add(1, 'day');
                }
              }
              else {
                element.append('<span class="fc-title" style="position:absolute; top:8px; left:3px;">' + (event.title || '&nbsp;') + '</span>');
              }
            }
            else {
              if (event.title != null && Drupal.settings.batCalendar[key].showBackgroundEventTitle) {
                element.append('<span class="fc-title" style="position:absolute; top:8px; left:3px;">' + (event.title || '&nbsp;') + '</span>');
              }
            }
          }
        }
      });
    });

    function datepicker() {
      var calendar = $(this).parent().parent().parent();

      $(this).after('<input type="text" style="height: 0px; border: 0px;" id="hiddenDate" class="datepicker" />');

      $(this).next().datepicker({
        dateFormat: 'mm/dd/yy',
        beforeShow: function(input, inst) {
          var cal = inst.dpDiv;
          var button = $(calendar).find('.fc-datepicker-button');
          var top  = button.offset().top + button.outerHeight() + 4;
          var left = button.offset().left;
          setTimeout(function() {
            cal.css({
              top: top,
              left: left
            });
          }, 10);
        },
        onSelect: function(date) {
          $(calendar).fullCalendar('gotoDate', date);
        },
        onClose: function(date) {
          $(this).remove();
        }
      })
      .datepicker('setDate', $(calendar).fullCalendar('getDate').format('MM/DD/YYYY'))
      .datepicker('show');
    }

    function isInsideBusinessHour(business_hours, day, start, end) {
      for (business_hour of business_hours) {
        if (business_hour.dow.indexOf(day) >= 0) {
          var business_start = moment.duration(business_hour.start);
          var business_end = moment.duration(business_hour.end);

          var start = moment.duration(start);
          var end = moment.duration(end);

          if (business_start._milliseconds <= start._milliseconds && business_end._milliseconds >= end._milliseconds) {
            return true;
          }
        }
      }

      return false;
    }

    function enumerateDaysBetweenDates(startDate, endDate) {
      var dates = [];

      var currDate = startDate.clone().startOf('day');
      var lastDate = endDate.clone().startOf('day');

      while (currDate.add(1, 'days').diff(lastDate) < 0) {
        dates.push(currDate.clone());
      }

      return dates;
    };

    $.fullCalendar.Calendar.prototype.computeBusinessHourEvents = function(wholeDay, input) {
      var view = this.getView();

      if (moment.duration(view.options.maxTime) > moment.duration('24:00')) {
        this.getView().end = view.start.clone().add(view.intervalDuration).add(1, 'day');
      }

      if (input === true) {
        return this.expandBusinessHourEvents(wholeDay, [ {} ]);
      }
      else if ($.isPlainObject(input)) {
        return this.expandBusinessHourEvents(wholeDay, [ input ]);
      }
      else if ($.isArray(input)) {
        return this.expandBusinessHourEvents(wholeDay, input, true);
      }
      else {
        return [];
      }
    };
  }
};

/**
 * Initialize the modal box.
 */
Drupal.batCalendar.Modal = function(element, key, eid, sd, ed, $unit_id) {
  Drupal.CTools.Modal.show('bat-modal-style');

  // The base url (which doesn't change) is used to identify our ajax instance.
  var base = Drupal.settings.basePath + '?q=' + Drupal.settings.pathPrefix + 'admin/bat/fullcalendar/';
  // Create a drupal ajax object that points to the event form.
  var element_settings = {
    url : base + $unit_id + '/event/' + Drupal.settings.batCalendar[key].eventType + '/' + eid + '/' + sd + '/' + ed,
    event : 'getResponse',
    progress : { type: 'throbber' }
  };

  // To make all calendars trigger correctly the getResponse event we need to
  // initialize the ajax instance with the global calendar table element.
  var calendars_table = $(element.el).closest('.calendar-set');

  // Create a new instance only once.
  // If it exists just override the url.
  if (Drupal.ajax[base + key] === undefined) {
    Drupal.ajax[base + key] = new Drupal.ajax(element_settings.url, calendars_table, element_settings);
  }
  else {
    Drupal.ajax[base + key].element_settings.url = element_settings.url;
    Drupal.ajax[base + key].options.url = element_settings.url;
  }
  // We need to trigger the AJAX getResponse manually because the
  // fullcalendar select event is not recognized by Drupal's AJAX.
  $(calendars_table).trigger('getResponse');
};

function saveBatEvent(event, revertFunc, calendars, key) {
  // The event has been moved - attempt to update it.
  var unit_id = event.resourceId.substring(1);

  // Retrieve all events for the unit and time we're dragging onto.
  var events_url = Drupal.settings.basePath + '?q=bat/v2/events&target_ids=' + unit_id + '&target_entity_type=bat_unit&start_date=' + event.start.format('YYYY-MM-DD HH:mm') +
                   '&end_date=' + event.end.format('YYYY-MM-DD HH:mm') + '&event_types=' + event.type;
  proceed = true;
  jQuery.ajax({
    url: events_url,
    success: function(data) {
      // Iterate over each event.
      $.each(data.events, function(index, existingEvent) {
        if (proceed && existingEvent.blocking && (existingEvent.bat_id != event.bat_id)) {
          // This is a blocking event that is not the one we're dragging, bail out!
          alert(Drupal.t('It appears that a conflicting event has been created. Refreshing events.'));
          proceed = false;
          // Refresh calendar events to show the conflicting event.
          $.each(calendars, function(key, value) {
            $(value[0]).fullCalendar('refetchEvents');
          });
        }
      });
    },
    failure: function() {
      alert(Drupal.t('Could not verify that this change is possible. Please try again.'));
      proceed = false;
    },
    async: false
  });

  // Only save the event if we didn't find any conflicts.
  if (proceed == true) {
    // Get session token.
    $.ajax({
      url: Drupal.settings.basePath + '?q=services/session/token',
      type: 'get',
      dataType: 'text',
      error:function (jqXHR, textStatus, errorThrown) {
        alert(Drupal.settings.batCalendar[key].errorMessage);
      },
      success: function (token) {
        // Update event, using session token.
        var events_url = Drupal.settings.basePath + '?q=bat/v2/events';
        $.ajax({
          type: 'PUT',
          url: events_url + '/' + event.bat_id,
          data: JSON.stringify({start_date: event.start.format('YYYY-MM-DD HH:mm'), end_date: event.end.format('YYYY-MM-DD HH:mm'), target_id: unit_id}),
          dataType: 'json',
          headers: {
            'X-CSRF-Token': token
          },
          contentType: 'application/json',
          error: function (jqXHR, textStatus, errorThrown) {
            alert(Drupal.settings.batCalendar[key].errorMessage);
            revertFunc();
          },
          success: function (request) {
            // Refresh calendar events.
            $.each(calendars, function(key, value) {
              $(value[0]).fullCalendar('refetchEvents');
            });
          }
        });
      }
    });
  }
  else {
    // We found a conflict, revert the event to its previous position.
    revertFunc();
    // Refresh calendar events.
    $.each(calendars, function(key, value) {
      $(value[0]).fullCalendar('refetchEvents');
    });
  }
}

})(jQuery);
