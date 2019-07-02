(function ($) {

// Define our objects.
Drupal.batCalendar = Drupal.batCalendar || {};
Drupal.batCalendar.Modal = Drupal.batCalendar.Modal || {};

var ajax = undefined;

Drupal.behaviors.bat_event = {
  attach: function(context) {

    var calendars = [];
    for (id in drupalSettings.batCalendar) {
      calendars[id] = new Array('#' + drupalSettings.batCalendar[id]['id']);
    }

    // Refresh the event once the modal is closed.
    $(window).on('dialog:beforeclose', function (e, dialog, $element) {
      $.each(calendars, function(key, value) {
        $(value[0]).fullCalendar('refetchEvents');
      });
    });

    $.each(calendars, function(key, value) {

      $(value[0]).once().fullCalendar({
        schedulerLicenseKey: drupalSettings.batCalendar[key].schedulerLicenseKey,
        themeSystem: drupalSettings.batCalendar[key].themeSystem,
        locale: drupalSettings.batCalendar[key].locale,
        slotWidth: drupalSettings.batCalendar[key].slotWidth,
        height: drupalSettings.batCalendar[key].calendarHeight,
        editable: drupalSettings.batCalendar[key].editable,
        selectable: drupalSettings.batCalendar[key].selectable,
        displayEventTime: false,
        eventStartEditable: drupalSettings.batCalendar[key].eventStartEditable,
        eventDurationEditable: drupalSettings.batCalendar[key].eventDurationEditable,
        dayNamesShort:[Drupal.t('Sun'), Drupal.t('Mon'), Drupal.t('Tue'), Drupal.t('Wed'), Drupal.t('Thu'), Drupal.t('Fri'), Drupal.t('Sat')],
        monthNames:[Drupal.t('January'), Drupal.t('February'), Drupal.t('March'), Drupal.t('April'), Drupal.t('May'), Drupal.t('June'), Drupal.t('July'), Drupal.t('August'), Drupal.t('September'), Drupal.t('October'), Drupal.t('November'), Drupal.t('December')],
        header: {
          left: drupalSettings.batCalendar[key].headerLeft,
          center: drupalSettings.batCalendar[key].headerCenter,
          right: drupalSettings.batCalendar[key].headerRight,
        },
        allDayDefault: drupalSettings.batCalendar[key].allDayDefault,
        businessHours: drupalSettings.batCalendar[key].businessHours,
        defaultView: drupalSettings.batCalendar[key].defaultView,
        selectConstraint: (drupalSettings.batCalendar[key].selectConstraint == null) ? undefined : drupalSettings.batCalendar[key].selectConstraint,
        minTime: drupalSettings.batCalendar[key].minTime,
        maxTime: drupalSettings.batCalendar[key].maxTime,
        hiddenDays: drupalSettings.batCalendar[key].hiddenDays,
        validRange: drupalSettings.batCalendar[key].validRange,
        defaultDate: $.fullCalendar.moment(drupalSettings.batCalendar[key].defaultDate),
        views: {
          timelineDay: {
            buttonText: drupalSettings.batCalendar[key].viewsTimelineDayButtonText,
            slotDuration: drupalSettings.batCalendar[key].viewsTimelineDaySlotDuration,
          },
          timelineSevenDay: {
            buttonText: drupalSettings.batCalendar[key].viewsTimelineSevenDayButtonText,
            duration: drupalSettings.batCalendar[key].viewsTimelineSevenDayDuration,
            slotDuration: drupalSettings.batCalendar[key].viewsTimelineSevenDaySlotDuration,
            type: 'timeline',
          },
          timelineTenDay: {
            buttonText: drupalSettings.batCalendar[key].viewsTimelineTenDayButtonText,
            duration: drupalSettings.batCalendar[key].viewsTimelineTenDayDuration,
            slotDuration: drupalSettings.batCalendar[key].viewsTimelineTenDaySlotDuration,
            type: 'timeline',
          },
          timelineThirtyDay: {
            buttonText: drupalSettings.batCalendar[key].viewsTimelineThirtyDayButtonText,
            duration: drupalSettings.batCalendar[key].viewsTimelineThirtyDayDuration,
            slotDuration: drupalSettings.batCalendar[key].viewsTimelineThirtyDaySlotDuration,
            type: 'timeline',
          },
          timeline365Day: {
            buttonText: drupalSettings.batCalendar[key].viewsTimeline365DayButtonText,
            duration: drupalSettings.batCalendar[key].viewsTimeline365DaySlotDuration,
            type: 'timeline',
          }
        },
        groupByResource: drupalSettings.batCalendar[key].groupByResource,
        groupByDateAndResource: drupalSettings.batCalendar[key].groupByDateAndResource,
        allDaySlot: drupalSettings.batCalendar[key].allDaySlot,
        firstDay: drupalSettings.batCalendar[key].firstDay,
        defaultTimedEventDuration: drupalSettings.batCalendar[key].defaultTimedEventDuration,
        customButtons: drupalSettings.batCalendar[key].customButtons,
        eventOrder: drupalSettings.batCalendar[key].eventOrder,
        titleFormat: drupalSettings.batCalendar[key].titleFormat,
        slotLabelFormat: drupalSettings.batCalendar[key].slotLabelFormat,
        resourceAreaWidth: drupalSettings.batCalendar[key].resourceAreaWidth,
        resourceLabelText: drupalSettings.batCalendar[key].resourceLabelText,
        resources: Drupal.url('bat_api/calendar-units?_format=json&types=' + drupalSettings.batCalendar[key].unitType + '&ids=' + drupalSettings.batCalendar[key].unitIds + '&event_type=' + drupalSettings.batCalendar[key].eventType),
        selectOverlap: function(event) {
          // Allow selections over background events, but not any other types of events.
          return event.rendering === 'background';
        },
        events: Drupal.url('bat_api/calendar-events?_format=json&unit_types=' + drupalSettings.batCalendar[key].unitType + '&unit_ids=' + drupalSettings.batCalendar[key].unitIds + '&event_types=' + drupalSettings.batCalendar[key].eventType + '&background=' + drupalSettings.batCalendar[key].background),
        windowResize: function(view) {
          $(this).fullCalendar('refetchEvents');
        },
        eventClick: function(event, jsEvent, view) {
          if (event.editable) {
            var unit_id = event.resourceId.substring(1);
            var sd = event.start.format('YYYY-MM-DD HH:mm');
            event.end.add(1, 'm');
            var ed = event.end.format('YYYY-MM-DD HH:mm');

            // Open the modal for edit
            Drupal.batCalendar.Modal(view, key, event.bat_id, sd, ed, unit_id);
          }
        },
        select: function(start, end, jsEvent, view, resource) {
          if (resource.create_event) {
            var unit_id = resource.id.substring(1);

            var ed = end.format('YYYY-MM-DD HH:mm');
            var sd = start.format('YYYY-MM-DD HH:mm');

            // Open the modal for edit
            Drupal.batCalendar.Modal(this, key, 0, sd, ed, unit_id);
          }

          $(value[0]).fullCalendar('unselect');
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
        eventAfterRender: function(event, element, view) {
          // Append event title when rendering as background.
          if (event.rendering == 'background' && event.fixed == 0) {
            if ((view.type == 'timelineThirtyDay' || view.type == 'timelineMonth' || view.type == 'timelineYear') && drupalSettings.batCalendar[key].repeatEventTitle) {
              var start = event.start.clone();
              start.subtract(start.hour(), 'hours').subtract(start.minute(), 'minutes');

              if (event.end === null) {
                var end = event.start.clone();
              }
              else {
                var end = event.end.clone();
              }

              var index = 0;

              // Event width.
              var width = element.width()
              // Event colspan number.
              var colspan = element.get(0).colSpan;

              if (event.end != null) {
                end.add(1, 'minute');
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
        }
      });
    });
  }
};

/**
 * Initialize the modal box.
 */
Drupal.batCalendar.Modal = function(element, key, eid, sd, ed, $unit_id) {
  // To make all calendars trigger correctly the getResponse event we need to
  // initialize the ajax instance with the global calendar table element.
  var calendars_table = $(element.el).closest('.calendar-set').get();

  var base = Drupal.url('admin/bat/fullcalendar/');
  // Create a drupal ajax object that points to the event form.
  var url = base + $unit_id + '/event/' + drupalSettings.batCalendar[0].eventType + '/' + eid + '/' + sd + '/' + ed;

  var element_settings = {
    url : url,
    event : 'getResponse',
    progress : { type: 'throbber' },
    selector: '#drupal-modal'
  };

  var response = {
    selector: '#drupal-modal',
    dialogOptions: drupalSettings.batCalendar[0].dialogOptions,
  };

  if (ajax == undefined) {
    ajax = new Drupal.Ajax(element_settings.url, calendars_table, element_settings);
  }
  else {
    ajax.url = url;
    ajax.options.url = url + '?' + Drupal.ajax.WRAPPER_FORMAT + '=drupal_ajax';
    ajax.element_settings.url = url;
  }

  Drupal.AjaxCommands.prototype.openDialog(ajax, response, 0);

  $('#drupal-modal').html(drupalSettings.batCalendar[0].dialogOptions.loading);

  // We need to trigger the AJAX getResponse manually because the
  // fullcalendar select event is not recognized by Drupal's AJAX.
  $(calendars_table).trigger('getResponse');
};

function saveBatEvent(event, revertFunc, calendars, key) {
  // The event has been moved - attempt to update it.
  var unit_id = event.resourceId.substring(1);

  // Retrieve all events for the unit and time we're dragging onto.
  var events_url = Drupal.url('bat_api/events?_format=json&target_ids=' + unit_id + '&target_entity_type=bat_unit&start_date=' + event.start.format('YYYY-MM-DD HH:mm') +
                   '&end_date=' + event.end.format('YYYY-MM-DD HH:mm') + '&event_types=' + event.type);
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
    $.get(Drupal.url('services/session/token'))
    .done(function (token) {

      var events_url = Drupal.url('bat_api/bat_event');
      $.ajax({
        type: 'GET',
        url: events_url + '/' + event.bat_id + '?_format=json',
        headers: {
          'Content-Type': 'application/hal+json',
          'X-CSRF-Token': token
        },
        error: function (jqXHR, textStatus, errorThrown) {
          alert(drupalSettings.batCalendar[key].errorMessage);
          revertFunc();
        },
        success: function (new_event) {
          new_event['event_dates'][0]['value'] = event.start.utc().format();
          new_event['event_dates'][0]['end_value'] = event.end.utc().add(1, 'minutes').format();
          new_event['event_bat_unit_reference'][0]['target_id'] = unit_id;

          new_event['_links'] = {
            type: {
              href: document.location.origin + '/rest/type/bat_event/' + new_event['type'][0]['target_id']
            }
          };

          // Update event.
          $.ajax({
            type: 'PUT',
            url: events_url + '/' + event.bat_id + '?_format=json',
            data: JSON.stringify(new_event),
            headers: {
              'Content-Type': 'application/hal+json',
              'X-CSRF-Token': token
            },
            error: function (jqXHR, textStatus, errorThrown) {
              alert(drupalSettings.batCalendar[key].errorMessage);
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
