<?php

/**
 * @file
 * Interface CalendarInterface
 */

namespace Drupal\bat;

/**
 * Handles querying and updating the availability information
 * relative to a single bookable unit.
 */
interface BatCalendarInterface {
  /**
   * Given a date range returns an array of Events. The heavy lifting really takes place in
   * the getRawDayData function - here we are simply acting as a factory for event objects.
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
   * @return EventInterface[]
   * An array of Event objects
   */
  public function getEvents(\DateTime $start_date, \DateTime $end_date);

  /**
   * Given an array of Events the calendar is updated with regards to the
   * events that are relevant to the Unit this calendar refers to.
   *
   * @param EventInterface[] $events
   *   An array of events to update the calendar with
   *
   * @param $store
   * The set of table names to use as an event store.
   */
  public function addEvents($events);

}
