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

  const VALID_STATE = 'valid_state';
  const INVALID_STATE = 'invalid_state';

  /**
   * @var array
   */
  public $included_set;

  public $excluded_set;


  public function __construct($included = array(), $excluded = array()) {
    $this->included = $included;
    $this->excluded = $excluded;
  }

  public function addMatch($unit, $reason = '') {
    $this->included_set[$unit] = $reason;
  }

  public function addMiss($unit, $reason = '') {
    $this->excluded_set[$unit] = $reason;
  }

  public function getIncluded(){
    return $this->included_set();
  }

  public function getExcluded(){
    return $this->excluded_set;
  }

  public function removeFromMatched($unit, $reason = '') {
    if (isset($this->included_set[$unit])) {
      // Remove a unit from matched and add to the missed set
      unset($this->include_set[$unit]);
      $this->addMiss($unit, $reason);
      return TRUE;
    } else {
      return FALSE;
    }
  }

}