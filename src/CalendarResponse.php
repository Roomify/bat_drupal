<?php

/**
 * @file
 * Class CalendarResponse
 */

namespace Drupal\bat;

use Drupal\bat\Unit;
use Drupal\bat\Constraint;

/**
 * A CalendarResponse contains the units that are matched or missed following
 * a search, together with the reason they are matched or missed.
 */
class CalendarResponse {


  public $matched = array();
  public $missed = array();


  public function __construct() {

  }

  public function addMatch(Unit $unit, $reason = '') {

  }

  public function addMiss(Unit $unit, $reason = '') {

  }

  public function applyConstraint(Constraint $constraint) {

  }


}