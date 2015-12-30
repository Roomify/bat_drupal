<?php

/**
 * @file
 * Class CheckInDayConstraint
 */

namespace Drupal\bat;

use Drupal\bat\Constraint;

/**
 *
 */
class CheckInDayConstraint extends Constraint {

	protected $check_in_day;

	public function __construct($check_in_day) {
	  $this->check_in_day = $check_in_day;
	}

	/**
	 * {@inheritdoc}
	 */
	public function applyConstraint() {
		return $this->calendar_response;
	}

}
