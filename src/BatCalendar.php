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

  public function __construct($store = array(), $unit_ids = array()) {
    $this->unit_ids = $unit_ids;
    $this->store = $store;
  }
}
