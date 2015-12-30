<?php

/**
 * @file
 * Class MinMaxConstraint
 */

namespace Drupal\bat;

use Drupal\bat\Constraint;

/**
 *
 */
class MinMaxConstraint extends Constraint {

  /**
   *
   */
  public $min_days = 0;

  /**
   *
   */
  public $max_days = 0;

  /**
   * @param $min_days
   * @param $max_days
   * @param $start_date
   * @param $end_date
   */
  public function __construct($min_days = 0, $max_days = 0, $start_date = NULL, $end_date = NULL) {
    $this->min_days = $min_days;
    $this->max_days = $max_days;
    $this->start_date = $start_date;
    $this->end_date = $end_date;
  }

  /**
   * {@inheritdoc}
   */
  public function applyConstraint(&$calendar_response) {
    $unit_id = 1;

    $included_set = $calendar_response->getIncluded();

    if (isset($included_set[$unit_id])) {
      $start_date = $calendar_response->getStartDate();
      $end_date = $calendar_response->getEndDate();

      $diff = $end_date->diff($start_date)->days;
      if (is_numeric($this->min_days)) {
        if ($diff < $this->min_days) {
          $calendar_response->removeFromMatched($included_set[$unit_id]['unit'], CalendarResponse::INVALID_STATE);
        }
      }
      if (is_numeric($this->max_days)) {
        if ($diff > $this->max_days) {
          $calendar_response->removeFromMatched($included_set[$unit_id]['unit'], CalendarResponse::INVALID_STATE);
        }
      }
    }
  }

}
