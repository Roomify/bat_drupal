<?php

/**
 * @file
 * Handles querying and updating the availability information
 * relative to a single bookable unit.
 */

namespace Drupal\bat_availability;

use Drupal\bat\BatCalendarInterface;

interface UnitCalendarInterface extends BatCalendarInterface {
  /**
   * Given a date range returns all states in that range - useful when we are
   * not interested in starting and ending dates but simply in states.
   *
   * @param DateTime $start_date
   *   The start day of the range.
   * @param DateTime $end_date
   *   The end date of our range.
   * @param bool $confirmed
   *   Whether include confirmed states or not.
   *
   * @return array
   *   An array of states within that range
   */
  public function getStates(\DateTime $start_date, \DateTime $end_date, $confirmed = FALSE);

  /**
   * Checks if an event is blocked, i.e. cannot be updated. This happens
   * when the event id is in the bat_booking_locks table and the new event_id
   * is not the same as the one that is locked.
   *
   * @param BookingEventInterface $event
   *   The event to check if it is blocked.
   *
   * @return bool
   *   TRUE if blocked, FALSE otherwise.
   */
  public function eventBlocked(BookingEventInterface $event);

  /**
   * Given a set of states (e.g. the desired states to accept a booking) we
   * compare against the states the unit is actually in.
   *
   * If the unit is in any state that is not in the list of desired states it
   * means there is a mismatch - hence no availability.
   *
   * @param DateTime $start_date
   *   The starting date for the search.
   * @param DateTime $end_date
   *   The end date for the search.
   * @param array $states
   *   The states we are interested in.
   *
   * @return bool
   *   Returns true if the date range provided does not have states other than
   * the ones we are interested in
   */
  public function stateAvailability(\DateTime $start_date, \DateTime $end_date, array $states = array());

  /**
   * Returns the default state.
   *
   * @return string
   *   The calendar default state.
   */
  public function getDefaultState();

  /**
   * Given an array of events removes events from the calendar setting the value
   * to the default.
   *
   * @param BookingEventInterface[] $events
   *   The events to remove from the database - an array of Booking Events
   */
  public function removeEvents($events);

}
