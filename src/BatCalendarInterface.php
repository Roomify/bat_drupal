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
   * @return BatEventInterface[]
   * An array of BookingEvent objects
   */
  public function getEvents(\DateTime $start_date, \DateTime $end_date);

  /**
   * Given a date range it returns all data within that range including the
   * start and end dates of states. The MySQL queries are kept simple and then
   * the data is cleared up.
   *
   * @param DateTime $start_date
   * The starting date
   *
   * @param DateTime $end_date
   * The end date of our range
   *
   * @return array
   * An array of the structure data[unitid][year][month][days][d1]..[d31]
   * as week as data[unitid][year][month][unique_states]
   */
  public function getRawDayData(\DateTime $start_date, \DateTime $end_date);

  /**
   * Given an array of BatEvents the calendar is updated with regards to the
   * events that are relevant to the Unit this calendar refers to
   *
   * @param BatEventInterface[] $events
   *   An array of events to update the calendar with
   *
   * @return array
   *   An array of response on whether event updates were successful or not
   */
  public function updateCalendar($events);

  /**
   * Adds an event to the calendar
   *
   * @param BatEventInterface $event
   *   An an event of type BookingEvent
   */
  public function addMonthEvent(BatEventInterface $event);

  /**
   * Checks if a month exists.
   *
   * @param int $month
   *   The month to check.
   * @param int $year
   *   The year to check.
   *
   * @return bool
   *   TRUE if the month is defined, FALSE otherwise.
   */
  public function monthDefined($month, $year);
}