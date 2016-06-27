(function ($) {

// Define our objects.
Drupal.batCalendar = Drupal.batCalendar || {};
Drupal.batCalendar.Modal = Drupal.batCalendar.Modal || {};

var ajax = undefined;

Drupal.behaviors.bat_event = {
  attach: function(context) {

    var calendars = [];
    calendars[0] = new Array('#calendar');

    // Refresh the event once the modal is closed.
    $(window).on('dialog:beforeclose', function (e, dialog, $element) {
      $.each(calendars, function(key, value) {
        $(value[0]).fullCalendar('refetchEvents');
      });
    });

    $.each(calendars, function(key, value) {

      $(value[0]).once().fullCalendar({
        schedulerLicenseKey: drupalSettings.batCalendar[0].schedulerLicenseKey,
        slotWidth: drupalSettings.batCalendar[0].slotWidth,
        height: drupalSettings.batCalendar[0].calendarHeight,
        editable: drupalSettings.batCalendar[0].editable,
        selectable: drupalSettings.batCalendar[0].selectable,
        displayEventTime: false,
        dayNamesShort:[Drupal.t('Sun'), Drupal.t('Mon'), Drupal.t('Tue'), Drupal.t('Wed'), Drupal.t('Thu'), Drupal.t('Fri'), Drupal.t('Sat')],
        monthNames:[Drupal.t('January'), Drupal.t('February'), Drupal.t('March'), Drupal.t('April'), Drupal.t('May'), Drupal.t('June'), Drupal.t('July'), Drupal.t('August'), Drupal.t('September'), Drupal.t('October'), Drupal.t('November'), Drupal.t('December')],
        header: {
          left: drupalSettings.batCalendar[0].headerLeft,
          center: drupalSettings.batCalendar[0].headerCenter,
          right: drupalSettings.batCalendar[0].headerRight,
        },
        businessHours: drupalSettings.batCalendar[0].businessHours,
        defaultView: drupalSettings.batCalendar[0].defaultView,
        selectConstraint: drupalSettings.batCalendar[0].selectConstraint,
        minTime: drupalSettings.batCalendar[0].minTime,
        maxTime: drupalSettings.batCalendar[0].maxTime,
        hiddenDays: drupalSettings.batCalendar[0].hiddenDays,
        defaultDate: $.fullCalendar.moment(drupalSettings.batCalendar[0].defaultDate),
        views: {
          timelineDay: {
            buttonText: drupalSettings.batCalendar[0].viewsTimelineDayButtonText,
            slotDuration: drupalSettings.batCalendar[0].viewsTimelineDaySlotDuration,
          },
          timelineSevenDay: {
            buttonText: drupalSettings.batCalendar[0].viewsTimelineSevenDayButtonText,
            duration: drupalSettings.batCalendar[0].viewsTimelineSevenDaySlotDuration,
            type: 'timeline',
          },
          timelineTenDay: {
            buttonText: drupalSettings.batCalendar[0].viewsTimelineTenDayButtonText,
            duration: drupalSettings.batCalendar[0].viewsTimelineTenDaySlotDuration,
            type: 'timeline',
          },
          timelineThirtyDay: {
            buttonText: drupalSettings.batCalendar[0].viewsTimelineThirtyDayButtonText,
            duration: drupalSettings.batCalendar[0].viewsTimelineThirtyDaySlotDuration,
            type: 'timeline',
          }
        },
        resourceAreaWidth: drupalSettings.batCalendar[0].resourceAreaWidth,
        resourceLabelText: drupalSettings.batCalendar[0].resourceLabelText,
        resources: '/bat_api/calendar-units?_format=json&types=' + drupalSettings.batCalendar[0].unitType + '&event_type=' + drupalSettings.batCalendar[0].eventType,
        selectOverlap: function(event) {
          // Allow selections over background events, but not any other types of events.
          return event.rendering === 'background';
        },
        events: '/bat_api/calendar-events?_format=json&unit_types=' + drupalSettings.batCalendar[0].unitType + '&event_types=' + drupalSettings.batCalendar[0].eventType + '&background=' + drupalSettings.batCalendar[0].background,
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
            Drupal.batCalendar.Modal(view, event.bat_id, sd, ed, unit_id);
          }
        },
        select: function(start, end, jsEvent, view, resource) {
          if (resource.create_event) {
            var unit_id = resource.id.substring(1);

            var ed = end.format('YYYY-MM-DD HH:mm');
            var sd = start.format('YYYY-MM-DD HH:mm');

            // Open the modal for edit
            Drupal.batCalendar.Modal(this, 0, sd, ed, unit_id);
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
              saveBatEvent(event, revertFunc, calendars);
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
            saveBatEvent(event, revertFunc, calendars);
          }
          else {
            revertFunc();
          }
        },
        eventAfterRender: function(event, element, view) {
          // Append event title when rendering as background.
          if (event.rendering == 'background' && event.fixed == 0) {
            if ((view.type == 'timelineThirtyDay' || view.type == 'timelineMonth' || view.type == 'timelineYear') && drupalSettings.batCalendar[0].repeatEventTitle) {
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
Drupal.batCalendar.Modal = function(element, eid, sd, ed, $unit_id) {
  // To make all calendars trigger correctly the getResponse event we need to
  // initialize the ajax instance with the global calendar table element.
  var calendars_table = $(element.el).closest('.calendar-set');

  var base = '/admin/bat/fullcalendar/';
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
    ajax.options.url = url;
    ajax.element_settings.url = url;
  }

  Drupal.AjaxCommands.prototype.openDialog(ajax, response, 0);

  $('#drupal-modal').html(drupalSettings.batCalendar[0].dialogOptions.loading);

  // We need to trigger the AJAX getResponse manually because the
  // fullcalendar select event is not recognized by Drupal's AJAX.
  $(calendars_table).trigger('getResponse');
};

function saveBatEvent(event, revertFunc, calendars) {
  // The event has been moved - attempt to update it.
  var unit_id = event.resourceId.substring(1);

  // Retrieve all events for the unit and time we're dragging onto.
  var events_url = drupalSettings.basePath + '?q=bat/v2/events&target_ids=' + unit_id + '&target_entity_type=bat_unit&start_date=' + event.start.format('YYYY-MM-DD HH:mm') +
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
      url: drupalSettings.basePath + '?q=services/session/token',
      type: 'get',
      dataType: 'text',
      error:function (jqXHR, textStatus, errorThrown) {
        alert(drupalSettings.batCalendar[0].errorMessage);
      },
      success: function (token) {
        // Update event, using session token.
        var events_url = drupalSettings.basePath + '?q=bat/v2/events';
        $.ajax({
          type: 'PUT',
          url: events_url + '/' + event.bat_id,
          data: JSON.stringify({start_date: event.start.format('YYYY-MM-DD HH:mm'), end_date: event.end.format('YYYY-MM-DD HH:mm'), target_id: unit_id}),
          dataType: 'json',
          beforeSend: function (request) {
            request.setRequestHeader('X-CSRF-Token', token);
          },
          contentType: 'application/json',
          error: function (jqXHR, textStatus, errorThrown) {
            alert(drupalSettings.batCalendar[0].errorMessage);
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
