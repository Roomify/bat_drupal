<?php

/**
 * @file
 * Interface ConstraintInterface
 */

namespace Drupal\bat;

/**
 * The Constraint Interface
 */
interface ConstraintInterface {

	/**
	 *
	 */
  public function applyConstraint(&$calendar_response);

  /**
	 * @param $start_date
	 */
  public function setStartDate(\DateTime $start_date);

  /**
	 * @return DateTime
	 */
  public function getStartDate();

  /**
	 * @param $end_date
	 */
  public function setEndDate(\DateTime $end_date);

  /**
	 * @return DateTime
	 */
  public function getEndDate();

  /**
	 * @param $valid_states
	 */
  public function setValidStates($valid_states);

  /**
	 * @return
	 */
  public function getValidStates();

  /**
	 * @return
	 */
  public function getAffectedUnits();

}
