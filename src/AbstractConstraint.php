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

  public $start_date;

  public $end_date;

  public $valid_states;

  public $affected_units;

  public $calendar_response;

  /**
	 * {@inheritdoc}
	 */
  public function setStartDate(\DateTime $start_date) {
  	$this->start_date = $start_date;
  }

  /**
	 * {@inheritdoc}
	 */
  public function getStartDate() {
  	return $this->start_date;
  }

  /**
	 * {@inheritdoc}
	 */
  public function setEndDate(\DateTime $end_date) {
  	$this->end_date = $end_date;
  }

  /**
	 * {@inheritdoc}
	 */
  public function getEndDate() {
  	return $this->end_date;
  }

  /**
	 * {@inheritdoc}
	 */
  public function setValidStates($valid_states) {
  	$this->valid_states = $valid_states;
  }

  /**
	 * {@inheritdoc}
	 */
  public function getValidStates() {
  	return $this->valid_states;
  }

  /**
	 * {@inheritdoc}
	 */
  public function getAffectedUnits() {
  	return $this->affected_units;
  }

  /**
	 * {@inheritdoc}
	 */
  public function applyConstraint(&$calendar_response) {
    return $this->calendar_response;
  }

}
