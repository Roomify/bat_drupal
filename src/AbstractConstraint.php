<?php

/**
 * @file
 * Class Constraint
 */

namespace Drupal\bat;


/**
 * A constraint acts as a filter that can be applied to a Calendar Response to
 * further reduce the set of matching units based on criteria beyond their
 * specific state over the time range the Calendar was queried.
 */
class AbstractConstraint implements ConstraintInterface {

  public $input_set = array();

  public $start_date;

  public $end_date;

  public $valid_states;

  public function applyConstraint() {

  }
}