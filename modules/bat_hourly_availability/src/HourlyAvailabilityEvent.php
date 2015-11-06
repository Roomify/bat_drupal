<?php

/**
 * @file
 * Class HourlyAvailabilityEvent.
 */

namespace Drupal\bat_hourly_availability;

/**
 * HourlyAvailabilityEvent
 */
class HourlyAvailabilityEvent {

  /**
   * The start date for the event.
   *
   * @var DateTime
   */
  public $start_date;

  /**
   * The end date for the event.
   *
   * @var DateTime
   */
  public $end_date;

  /**
   * Construct the HourlyAvailabilityEvent instance.
   *
   * @param DateTime $start_date
   *   The start date of the event.
   * @param DateTime $end_date
   *   The end date of the event.
   */
  public function __construct($start_date, $end_date) {
    $this->start_date = $start_date;
    $this->end_date = $end_date;
  }

  /**
   * Return event duration.
   *
   * @return DateInterval
   */
  public function getDuration() {
    return $this->start_date->diff($this->end_date);
  }

}
