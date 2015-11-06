<?php

/**
 * @file
 * Class HourlyAvailabilityEvent.
 */

namespace Drupal\bat_hourly_availability;

/**
 *
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
   * @var DateInterval
   */
  public $duration;

  public function __construct($start_date, $end_date) {
    $this->start_date = $start_date;
    $this->end_date = $end_date;

    $this->duration = $start_date->diff($end_date);
  }

}
