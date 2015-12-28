<?php

/**
 * @file
 * Class Event
 */

namespace Drupal\bat;

class Event extends AbstractEvent {

  // Redeclaring constants used in AbstractEvent because of no clean way to
  // have constants inherited;
  const BAT_DAY = 'bat_day';
  const BAT_HOUR = 'bat_hour';
  const BAT_MINUTE = 'bat_minute';
  const BAT_HOURLY = 'bat_hourly';
  const BAT_DAILY = 'bat_daily';

  /**
   * Event constructor.
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param $unit
   * @param $value
   */
  public function __construct(\DateTime $start_date, \DateTime $end_date, $unit = NULL, $value = 0) {
    $this->unit_id = $unit;
    $this->start_date = clone($start_date);
    $this->end_date = clone($end_date);
    $this->value = $value;

  }
}
