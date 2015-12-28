<?php

/**
 * @file
 * Class BatCalendar
 */

namespace Drupal\bat;

/**
 * Handles querying and updating the availability information
 * relative to a single bookable unit based on BAT's data structure
 */
class BatCalendar extends BatAbstractCalendar {

  public function __construct($unit_ids, $store, $default_value = 0) {
    $this->unit_ids = $unit_ids;
    $this->store = $store;
    $this->default_value = $default_value;
  }
}
