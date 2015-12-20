<?php

/**
 * @file
 * Class BatGranularEvent
 */

namespace Drupal\bat;

class BatGranularEvent extends BatAbstractGranularEvent {

  // Redeclaring constants used in BatAbstractEvent because of no clean way to
  // have constants inherited;
  const BAT_DAY = 'bat_day';
  const BAT_HOUR = 'bat_hour';
  const BAT_MINUTE = 'bat_minute';
  const BAT_HOURLY = 'bat_hourly';
  const BAT_DAILY = 'bat_daily';

  public function __construct($unit, \DateTime $start_date, \DateTime $end_date, $value) {
    $this->unit_id = $unit;
    $this->start_date = clone($start_date);
    $this->end_date = clone($end_date);
    $this->value = $value;

  }
}
