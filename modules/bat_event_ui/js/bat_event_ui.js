(function ($) {
// define object
Drupal.BatAvailability = Drupal.BatAvailability || {};
Drupal.BatAvailability.Modal = Drupal.BatAvailability.Modal || {};

Drupal.behaviors.bat_availability = {
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
        resources: [{id: 'a', title: 'Room 101'}],
        selectOverlap: function(event) {
          // allowing selections over background events but not allowing selections over any other types of events
          return event.rendering === 'background';
        },
        events: [
          { id: '1', resourceId: 'a', start: '2016-01-07T02:00:00', end: '2016-01-07T07:00:00', title: 'event 1' },
        ],
        windowResize: function(view) {
          $(this).fullCalendar('refetchEvents');
        },
        eventClick: function(calEvent, jsEvent, view) {
          // Getting the Unix timestamp - JS will only give us milliseconds
          if (calEvent.end === null) {
            //We are probably dealing with a single day event
            calEvent.end = calEvent.start;
          }

          var sd = calEvent.start.unix();
          var ed = calEvent.end.unix();
          // Open the modal for edit
          Drupal.BatAvailability.Modal(view, calEvent.id, sd, ed);
        },
        select: function(start, end, jsEvent, view) {
          var ed = end.subtract(1, 'days');
          var sd = start.unix();
          ed = end.unix();
          // Open the modal for edit
          Drupal.BatAvailability.Modal(this, -2, sd, ed);
          $(value[0]).fullCalendar('unselect');
        },
        eventRender: function(event, el, view) {
          // Remove Time from events.
          el.find('.fc-time').remove();
        },
        eventAfterRender: function(event, element, view) {
          // Hide events that are outside this month.
          if (event.start.month() != view.intervalStart.month()) {
            element.css('visibility', 'hidden');
          }
        }
      });
    });
  }
};

/**
* Initialize the modal box.
*/
Drupal.BatAvailability.Modal = function(element, eid, sd, ed) {
  // prepare the modal show with the bat-availability settings.
  Drupal.CTools.Modal.show('bat-modal-style');
  // base url the part that never change is used to identify our ajax instance
  var base = Drupal.settings.basePath + '?q=admin/bat/units/unit/';
  // Create a drupal ajax object that points to the unit availability form.
  var element_settings = {
    url : base + Drupal.settings.batAvailability.unitID + '/event/' + eid + '/' + sd + '/' + ed,
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
