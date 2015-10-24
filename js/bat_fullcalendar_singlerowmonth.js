(function ($) {

	var FC = $.fullCalendar;
	var View = FC.views.basic.class;
	var singleRowMonth;

	singleRowMonth = View.extend({
	  computeRange: function(date) {
	    var range = View.prototype.computeRange.call(this, date);
	    range.end.add(30, 'days');

	    return range;
	  },

	  renderDates: function() {
	    this.dayNumbersVisible = true;
	    this.dayGrid.numbersVisible = true;
	    this.dayGrid.colHeadFormat = 'ddd';

	    this.el.addClass('fc-basic-view').html(this.renderHtml());

	    this.headRowEl = this.el.find('thead .fc-row');

	    this.scrollerEl = this.el.find('.fc-day-grid-container');
	    this.dayGrid.coordMap.containerEl = this.scrollerEl;

	    this.dayGrid.setElement(this.el.find('.fc-day-grid'));
	    this.dayGrid.renderDates(this.hasRigidRows());
	  },
	});

	FC.views.singleRowMonth = singleRowMonth;

})(jQuery);
