<?php

/**
 * @file
 * Class Calendar
 */

namespace Drupal\bat;

use Drupal\bat\Store;

/**
 * Handles querying and updating the availability information
 * relative to a single bookable unit based on BAT's data structure
 */
class Calendar extends AbstractCalendar {

  public function __construct($units, $store, $default_value = 0) {
    $this->units = $units;
    $this->store = $store;
    $this->default_value = $default_value;
  }
}
