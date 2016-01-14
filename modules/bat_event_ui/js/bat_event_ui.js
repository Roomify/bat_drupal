(function ($) {
// define object
Drupal.BatEvent = Drupal.BatEvent || {};
Drupal.BatEvent.Modal = Drupal.BatEvent.Modal || {};

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

    // refresh the events once the modal is closed
    $(document).one("CToolsDetachBehaviors", function() {
      $.each(calendars, function(key, value) {
        $(value[0]).fullCalendar('refetchEvents');
      });
    });

    $.each(calendars, function(key, value) {

      $(value[0]).once().fullCalendar({
        schedulerLicenseKey: Drupal.settings.batCalendar.schedulerLicenseKey,
        height: 500,
        editable: true,
        selectable: true,
        dayNamesShort:[Drupal.t("Sun"), Drupal.t("Mon"), Drupal.t("Tue"), Drupal.t("Wed"), Drupal.t("Thu"), Drupal.t("Fri"), Drupal.t("Sat")],
        monthNames:[Drupal.t("January"), Drupal.t("February"), Drupal.t("March"), Drupal.t("April"), Drupal.t("May"), Drupal.t("June"), Drupal.t("July"), Drupal.t("August"), Drupal.t("September"), Drupal.t("October"), Drupal.t("November"), Drupal.t("December")],
        //defaultDate: moment([value[2],phpmonth-1]),
        header:{
          left: 'today, prev, next',
          center: 'title',
          right: 'timelineDay, timelineTenDay, timelineMonth, timelineYear',
        },
        businessHours: businessHours,
        defaultView: 'timelineDay',
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
        resources: '/bat/v2/units-calendar?types=' + Drupal.settings.batEvent.unitType,
        selectOverlap: function(event) {
          // allowing selections over background events but not allowing selections over any other types of events
          return event.rendering === 'background';
        },
        events: '/bat/v2/events-calendar?unit_types=' + Drupal.settings.batEvent.unitType + '&event_types=' + Drupal.settings.batEvent.eventType,
        windowResize: function(view) {
          $(this).fullCalendar('refetchEvents');
        },
        eventClick: function(event, jsEvent, view) {
          var unit_id = event.resourceId.substring(1);
          var sd = event.start.format('YYYY-MM-DD HH:mm');
          event.end.add(1, 'm');
          var ed = event.end.format('YYYY-MM-DD HH:mm');

          // Open the modal for edit
          Drupal.BatEvent.Modal(view, event.bat_id, sd, ed, unit_id);
        },
        select: function(start, end, jsEvent, view, resource) {
          var unit_id = resource.id.substring(1);

          var ed = end.format('YYYY-MM-DD HH:mm');
          var sd = start.format('YYYY-MM-DD HH:mm');

          // Open the modal for edit
          Drupal.BatEvent.Modal(this, 0, sd, ed, unit_id);
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
          return !stillEvent.blocking;
        }
      });
    });
  }
};

/**
 * Initialize the modal box.
 */
Drupal.BatEvent.Modal = function(element, eid, sd, ed, $unit_id) {
  Drupal.CTools.Modal.show('bat-modal-style');
  // base url the part that never change is used to identify our ajax instance
  var base = Drupal.settings.basePath + '?q=admin/bat/calendar/';
  // Create a drupal ajax object that points to the event form.
  var element_settings = {
    url : base + $unit_id + '/event/' + Drupal.settings.batEvent.eventType + '/' + eid + '/' + sd + '/' + ed,
    event : 'getResponse',
    progress : { type: 'throbber' }
  };
  // To made all calendars trigger correctly the getResponse event we need to
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

})(jQuery);
