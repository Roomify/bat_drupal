<?php

/**
 * @file
 * Interface BatCalendarInterface
 */

namespace Drupal\bat;

/**
 * Handles querying and updating the availability information
 * relative to a single bookable unit.
 */
interface BatCalendarInterface {
  /**
   * Given a date range returns an array of BatEvents. The heavy lifting really takes place in
   * the getRawDayData function - here we are simply acting as a factory for event objects
   *
   * @param $start_date
   * The starting date
   *
   * @param $end_date
   * The end date of our range
   *
   * @param $store
   * The set of table names from which to retrieve data
   *
   * @return BatEventInterface[]
   * An array of BatEvent objects
   */
  public function getEventDates(\DateTime $start_date, \DateTime $end_date, $store);

  /**
   * Given an array of BatEvents the calendar is updated with regards to the
   * events that are relevant to the Unit this calendar refers to
   *
   * @param BatEventInterface[] $events
   *   An array of events to update the calendar with
   *
   * @param Boolean remove
   *   If set to TRUE the events are removed from calendar and state set to default
   *
   * @return array
   *   An array of response on whether event updates were successful or not
   */
  public function updateCalendar($events, $remove = FALSE);

}
