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

  /**
   * @var
   */
  protected $check_in_day;

  /**
   * @param $units
   * @param $check_in_day
   */
  public function __construct($units, $check_in_day) {
    parent::__construct($units);

    $this->check_in_day = $check_in_day;
  }

  /**
   * {@inheritdoc}
   */
  public function applyConstraint(&$calendar_response) {
    parent::applyConstraint($calendar_response);

    if ($this->start_date <= $calendar_response->getStartDate() || $this->end_date >= $calendar_response->getEndDate()) {
      $units = $this->getUnits();

      $included_set = $calendar_response->getIncluded();

      foreach ($included_set as $unit_id => $set) {
        if (isset($units[$unit_id]) || empty($units)) {
          $start_date = $calendar_response->getStartDate();

          if ($this->check_in_day !== $start_date->format('N')) {
            $calendar_response->removeFromMatched($included_set[$unit_id]['unit'], CalendarResponse::INVALID_STATE);

            $this->affected_units[$unit_id] = $included_set[$unit_id]['unit'];
          }
        }
      }
    }
  }

}
