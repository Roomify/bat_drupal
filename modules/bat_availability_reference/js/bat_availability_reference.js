(function ($) {


  Drupal.behaviors.bat_availability_reference = {
    attach: function(context) {
      var today = moment();

      $('.cal').once('cal', function() {
        var lastSource;
        var cal_id = $(this).siblings('.availability-title').attr('id');

        $(this).fullCalendar({
          editable: false,
          dayNamesShort:[Drupal.t("Sun"), Drupal.t("Mon"), Drupal.t("Tue"), Drupal.t("Wed"), Drupal.t("Thu"), Drupal.t("Fri"), Drupal.t("Sat")],
          monthNames:[Drupal.t("January"), Drupal.t("February"), Drupal.t("March"), Drupal.t("April"), Drupal.t("May"), Drupal.t("June"), Drupal.t("July"), Drupal.t("August"), Drupal.t("September"), Drupal.t("October"), Drupal.t("November"), Drupal.t("December")],
          defaultDate: today,
          firstDay: Drupal.settings.batAvailabilityRef[cal_id].firstDay,
          header:{
            left: 'today',
            center: 'title',
            right: 'prev, next'
          },
          viewRender: function(view, element) {
            if (view.name == 'month') {
              view.calendar.removeEvents();

              var url = '?q=bat/v1/availability&units=' + Drupal.settings.batAvailabilityRef[cal_id].unitID.join() + '&start_date=' + view.intervalStart.get('year') + '-' + (view.intervalStart.get('month') + 1) + '-01&duration=1M';
              $.ajax({
                url: url,
                success: function(data) {
                  events = data['events'];

                  for (var index = 0; index < Drupal.settings.batAvailabilityRef[cal_id].unitID.length; index++) {
                    events_array = events[Drupal.settings.batAvailabilityRef[cal_id].unitID[index]];

                    for (i in events_array) {
                      events_array[i].end = moment(events_array[i].end).subtract(1, 'days').format();
                    }
                    view.calendar.addEventSource(events_array);
                  }
                }
              });
            }
          },
          eventRender: function(event, el, view) {
            // Remove Time from events.
            el.find('.fc-time').remove();

            // Add a class if the event start it is not "AV" or "N/A".
            if (el.hasClass('fc-start') && this.id != 1 && this.id != 0) {
              el.append('<div class="event-start"/>');
              el.find('.event-start').css('border-top-color', this.color);
            }

            // Add a class if the event end and it is not "AV" or "N/A".
            if (el.hasClass('fc-end') && this.id != 1 && this.id != 0) {
              el.append('<div class="event-end"/>');
              el.find('.event-end').css('border-top-color', this.color);
            }
          },
          eventAfterRender: function(event, element, view) {
            // Hide events that are outside this month.
            if (event.start.month() != view.intervalStart.month()) {
              element.css('visibility', 'hidden');
              return;
            }

            // Event width.
            var width = element.parent().width()
            // Event colspan number.
            var colspan = element.parent().get(0).colSpan;
            // Single cell width.
            var cell_width = width/colspan;
            var half_cell_width = cell_width/2;

            // Move events between table margins.
            element.css('margin-left', half_cell_width);
            element.css('margin-right', -(half_cell_width));

            // Calculate width event to add end date triangle.
            width_event = element.children('.fc-content').width();

            // Add a margin left to the top triangle.
            element.children().closest('.event-end').css('margin-left', width_event-14);

            if (element.parent().index() == 0) {
              if (element.hasClass('fc-start')) {
                var row_index = element.closest('.fc-row').index('.fc-row');
                if (row_index >= 2) {
                  var event_color = $(view.dayGrid.rowEls[row_index - 2]).find('.fc-day-grid-event').last().css('background-color');
                  var event_text = $(view.dayGrid.rowEls[row_index - 2]).find('.fc-day-grid-event').last().find('.fc-title').text();

                  var new_event = $('<a class="fc-day-grid-event fc-event" style="background-color: ' + event_color + '; width: ' + (half_cell_width-3) + 'px; margin: -16px 0 0 0;"><div class="fc-content"><span class="fc-title">' + event_text + '</span></div></a>');
                  element.parent().append(new_event);

                  var event_end = $(view.dayGrid.rowEls[row_index - 2]).find('.fc-day-grid-event').last().find('.event-end').prop('outerHTML');
                  if (event_end != undefined) {
                    $(view.dayGrid.rowEls[row_index - 2]).find('.fc-day-grid-event').last().find('.event-end').remove();

                    new_event.append(event_end);
                    new_event.find('.event-end').css('margin-left', half_cell_width - 16);
                  }
                }
              }
            }

            if (element.parent().index() == element.parent().parent().children('td').length - 1) {
              element.css('margin-right', 0);
            }

            // If the event end in a next row.
            if (element.hasClass('fc-not-end')) {
              element.css('margin-right', 0);
            }
            // If the event start in a previous row.
            if (element.hasClass('fc-not-start')) {
              element.css('margin-left', 0);
              element.children().closest('.event-end').css('margin-left', (colspan * cell_width) + half_cell_width - 14);
            }
          }
        });

      });


      // Resize takes care of some quirks on occasion
      $(window).resize();

    }
  };
})(jQuery);
