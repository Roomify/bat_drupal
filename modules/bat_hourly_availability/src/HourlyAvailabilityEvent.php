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
   * @var DateTime
   */
  public $start_date;

  /**
   * @var DateTime
   */
  public $end_date;

  /**
   * Construct the HourlyAvailabilityEvent instance.
   *
   * @param DateTime $start_date
   * @param DateTime $end_date
   */
  public function __construct($start_date, $end_date) {
    $this->start_date = $start_date;
    $this->end_date = $end_date;
  }

  /**
   * @return DateInterval
   */
  public function getDuration() {
    return $this->start_date->diff($this->end_date);
  }

}
