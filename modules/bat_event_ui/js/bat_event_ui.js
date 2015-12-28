(function ($) {
// define object
Drupal.BatAvailability = Drupal.BatAvailability || {};
Drupal.BatAvailability.Modal = Drupal.BatAvailability.Modal || {};

Drupal.behaviors.bat_availability = {
  attach: function(context) {

    unit_id = Drupal.settings.batAvailability.unitID;

    // Current month is whatever comes through -1 since js counts months starting from 0
    currentMonth = Drupal.settings.batCalendar.currentMonth - 1;
    currentYear = Drupal.settings.batCalendar.currentYear;
    firstDay = Drupal.settings.batCalendar.firstDay;

    // The first month on the calendar
    month1 = currentMonth;
    year1 = currentYear;

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
    calendars[0] = new Array('#calendar', month1, year1);

    // refresh the events once the modal is closed
    $(document).one("CToolsDetachBehaviors", function() {
      $.each(calendars, function(key, value) {
        $(value[0]).fullCalendar('refetchEvents');
      });
    });

    $.each(calendars, function(key, value) {
      // phpmonth is what we send via the url and need to add one since php handles
      // months starting from 1 not zero
      phpmonth = value[1]+1;

      $(value[0]).once().fullCalendar({
        editable: false,
        selectable: true,
        height: 400,
        dayNamesShort:[Drupal.t("Sun"), Drupal.t("Mon"), Drupal.t("Tue"), Drupal.t("Wed"), Drupal.t("Thu"), Drupal.t("Fri"), Drupal.t("Sat")],
        monthNames:[Drupal.t("January"), Drupal.t("February"), Drupal.t("March"), Drupal.t("April"), Drupal.t("May"), Drupal.t("June"), Drupal.t("July"), Drupal.t("August"), Drupal.t("September"), Drupal.t("October"), Drupal.t("November"), Drupal.t("December")],
        firstDay: firstDay,
        defaultDate: moment([value[2],phpmonth-1]),
        header:{
          left: 'title',
          center: 'month, agendaWeek, agendaDay',
          right: 'today, prev, next',
        },
        businessHours: businessHours,
        selectOverlap: function(event) {
          // allowing selections over background events but not allowing selections over any other types of events
          return event.rendering === 'background';
        },
        viewRender: function(view, element) {
          view.calendar.removeEvents();

          if (view.name == 'month') {
            var url = Drupal.settings.basePath + '?q=bat/v1/availability&units=' + unit_id + '&start_date=' + moment(view.intervalStart).format('YYYY') + '-' + moment(view.intervalStart).format('M') + '-01&duration=1M';
            $.ajax({
              url: url,
              success: function(data) {
                events = data['events'];

                for (i in events[unit_id]) {
                  events[unit_id][i].end = moment(events[unit_id][i].end).subtract(1, 'days').format();
                }

                view.calendar.addEventSource(events[unit_id]);
              }
            });
          }
          else if (view.name == 'agendaDay') {
            var url_day = Drupal.settings.basePath + '?q=bat/units/unit/' + unit_id + '/day-availability/json/' + moment(view.start).format();
 
            $.ajax({
              url: url_day,
              dataType: 'json',
              success: function(data) {
                view.calendar.addEventSource(data);
              }
            });
          }
          else if (view.name == 'agendaWeek') {
            var url_week = Drupal.settings.basePath + '?q=bat/units/unit/' + unit_id + '/day-availability/json/' + moment(view.start).format() + '/7D';
 
            $.ajax({
              url: url_week,
              dataType: 'json',
              success: function(data) {
                view.calendar.addEventSource(data);
              }
            });
          }
        },
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
