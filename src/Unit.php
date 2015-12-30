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

	/**
	 * @param $unit_id
	 * @param $default_value
	 * @param $constraints
	 */
  public function __construct($unit_id, $default_value, $constraints) {
    $this->unit_id = $unit_id;
    $this->default_value = $default_value;
    $this->constraints = $constraints;
  }

}
