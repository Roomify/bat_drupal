<?php

/**
 * @file
 * Interface UnitInterface
 */

namespace Drupal\bat;

use Drupal\bat\AbstractUnit;

/**
 * The basic BAT unit interface.
 */
class Unit extends AbstractUnit {

  public function __construct($unit_id, $default_value) {
    $this->unit_id = $unit_id;
    $this->default_value = $default_value;
  }
}
