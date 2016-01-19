(function ($) {

// Define our objects.
Drupal.batCalendar = Drupal.batCalendar || {};
Drupal.batCalendar.Modal = Drupal.batCalendar.Modal || {};

Drupal.behaviors.bat_event = {
  attach: function(context) {

    openingTime = '';

    if (openingTime.length === 0) {
      businessHours = {
        start: '00:00',
        end: '24:00',
        dow: [0, 1, 2, 3, 4, 5, 6],
      };
    }
    else {
      businessHours = {
        start: openingTime.opening,
        end: openingTime.closing,
        dow: openingTime.dow
      };
    }

    var calendars = [];
    calendars[0] = new Array('#calendar');

    // Refresh the event once the modal is closed.
    $(document).one("CToolsDetachBehaviors", function() {
      $.each(calendars, function(key, value) {
        $(value[0]).fullCalendar('refetchEvents');
      });
    });

    $.each(calendars, function(key, value) {

      $(value[0]).once().fullCalendar({
        schedulerLicenseKey: Drupal.settings.batCalendar.schedulerLicenseKey,
        height: Drupal.settings.batCalendar.calendarHeight,
        editable: Drupal.settings.batCalendar.editable,
        selectable: Drupal.settings.batCalendar.selectable,
        dayNamesShort:[Drupal.t("Sun"), Drupal.t("Mon"), Drupal.t("Tue"), Drupal.t("Wed"), Drupal.t("Thu"), Drupal.t("Fri"), Drupal.t("Sat")],
        monthNames:[Drupal.t("January"), Drupal.t("February"), Drupal.t("March"), Drupal.t("April"), Drupal.t("May"), Drupal.t("June"), Drupal.t("July"), Drupal.t("August"), Drupal.t("September"), Drupal.t("October"), Drupal.t("November"), Drupal.t("December")],
        header: {
          left: Drupal.settings.batCalendar.headerLeft,
          center: Drupal.settings.batCalendar.headerCenter,
          right: Drupal.settings.batCalendar.headerRight,
        },
        businessHours: businessHours,
        defaultView: Drupal.settings.batCalendar.defaultView,
        views: {
          timelineDay: {
            buttonText: Drupal.settings.batCalendar.viewsTimelineDayButtonText,
            slotDuration: Drupal.settings.batCalendar.viewsTimelineDaySlotDuration,
          },
          timelineTenDay: {
            type: Drupal.settings.batCalendar.viewsTimelineTenDayButtonText,
            duration: Drupal.settings.batCalendar.viewsTimelineTenDaySlotDuration,
          }
        },
        resourceAreaWidth: Drupal.settings.batCalendar.resourceAreaWidth,
        resourceLabelText: Drupal.settings.batCalendar.resourceLabelText,
        resources: '/bat/v2/units-calendar?types=' + Drupal.settings.batCalendar.unitType,
        selectOverlap: function(event) {
          // Allow selections over background events, but not any other types of events.
          return event.rendering === 'background';
        },
        events: '/bat/v2/events-calendar?unit_types=' + Drupal.settings.batCalendar.unitType + '&event_types=' + Drupal.settings.batCalendar.eventType,
        windowResize: function(view) {
          $(this).fullCalendar('refetchEvents');
        },
        eventClick: function(event, jsEvent, view) {
          var unit_id = event.resourceId.substring(1);
          var sd = event.start.format('YYYY-MM-DD HH:mm');
          event.end.add(1, 'm');
          var ed = event.end.format('YYYY-MM-DD HH:mm');

          // Open the modal for edit
          Drupal.batCalendar.Modal(view, event.bat_id, sd, ed, unit_id);
        },
        select: function(start, end, jsEvent, view, resource) {
          var unit_id = resource.id.substring(1);

          var ed = end.format('YYYY-MM-DD HH:mm');
          var sd = start.format('YYYY-MM-DD HH:mm');

          // Open the modal for edit
          Drupal.batCalendar.Modal(this, 0, sd, ed, unit_id);
          $(value[0]).fullCalendar('unselect');
        },
        eventRender: function(event, el, view) {
          // Remove Time from events.
          el.find('.fc-time').remove();

          // Append event title when rendering as background.
          if (event.rendering == 'background' && event.fixed == 0) {
            el.append('<span class="fc-title">' + (event.title || '&nbsp;') + '</span>');
          }
        },
        eventOverlap: function(stillEvent, movingEvent) {
          // Prevent events from being drug over blocking events.
          return !stillEvent.blocking && (stillEvent.type == movingEvent.type);
        },
        eventDrop: function(event, delta, revertFunc) {
          // Prevent events from being dropped over unit types row.
          if (event.resourceId.match(/^S[0-9]+$/)) {
            saveBatEvent(event, revertFunc, calendars);
          }
          else {
            revertFunc();
          }
        },
        eventResize: function(event, delta, revertFunc) {
          saveBatEvent(event, revertFunc, calendars);
        }
      });
    });
  }
};

/**
 * Initialize the modal box.
 */
Drupal.batCalendar.Modal = function(element, eid, sd, ed, $unit_id) {
  Drupal.CTools.Modal.show('bat-modal-style');
  // base url the part that never change is used to identify our ajax instance
  var base = Drupal.settings.basePath + '?q=admin/bat/fullcalendar/';
  // Create a drupal ajax object that points to the event form.
  var element_settings = {
    url : base + $unit_id + '/event/' + Drupal.settings.batCalendar.eventType + '/' + eid + '/' + sd + '/' + ed,
    event : 'getResponse',
    progress : { type: 'throbber' }
  };

  console.log(element_settings);

  // To make all calendars trigger correctly the getResponse event we need to
  // initialize the ajax instance with the global calendar table element.
  var calendars_table = $(element.el).closest('.calendar-set');

  // create new instance only once if exists just override the url
  if (Drupal.ajax[base] === undefined) {
    Drupal.ajax[base] = new Drupal.ajax(element_settings.url, calendars_table, element_settings);
  }
  else {
    Drupal.ajax[base].element_settings.url = element_settings.url;
    Drupal.ajax[base].options.url = element_settings.url;
  }
  // We need to trigger manually the AJAX getResponse due fullcalendar select
  // event is not recognized by Drupal AJAX
  $(calendars_table).trigger('getResponse');
};

function saveBatEvent(event, revertFunc, calendars) {
  // The event has been moved - attempt to update it.
  var unit_id = event.resourceId.substring(1);

  // Retrieve all events for the unit and time we're dragging onto.
  var events_url = '/bat/v2/events?unit_ids=' + unit_id + '&start_date=' + event.start.format('YYYY-MM-DD HH:mm') +
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
      url:"/services/session/token",
      type:"get",
      dataType:"text",
      error:function (jqXHR, textStatus, errorThrown) {
        alert(errorThrown);
      },
      success: function (token) {
        // Update event, using session token.
        var events_url = '/bat/v2/events';
        $.ajax({
          type: "PUT",
          url: events_url + '/' + event.bat_id,
          data: JSON.stringify({start_date: event.start.format('YYYY-MM-DD HH:mm'), end_date: event.end.format('YYYY-MM-DD HH:mm'), unit_id: unit_id}),
          dataType: 'json',
          beforeSend: function (request) {
            request.setRequestHeader("X-CSRF-Token", token);
          },
          contentType: 'application/json',
          error: function (jqXHR, textStatus, errorThrown) {
            alert(errorThrown);
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
