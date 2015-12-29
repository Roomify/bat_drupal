<?php

/**
 * @file
 * Class AbstractUnit
 */

namespace Drupal\bat;

use Drupal\bat\UnitInterface;

abstract class AbstractUnit implements UnitInterface {

  protected $unit_id;

  protected $default_value;

  public function getUnitId() {
    return $this->unit_id;
  }

  public function setUnitId($unit_id) {
    $this->unit_id = $unit_id;
  }

  public function getDefaultValue() {
    return $this->default_value;
  }

  public function setDefaultValue($default_value) {
    $this->default_value = $default_value;
  }
}
