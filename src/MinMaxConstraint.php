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
   * @var int
   */
  public $min_days = 0;

  /**
   * @var int
   */
  public $max_days = 0;

  /**
   * @var int
   */
  public $checkin_day = NULL;

  /**
   * @param $min_days
   * @param $max_days
   * @param $start_date
   * @param $end_date
   * @param $checkin_day
   */
  public function __construct($units, $min_days = 0, $max_days = 0, $start_date = NULL, $end_date = NULL, $checkin_day = NULL) {
    parent::__construct($units);

    $this->min_days = $min_days;
    $this->max_days = $max_days;
    $this->start_date = $start_date;
    $this->end_date = $end_date;
    $this->checkin_day = $checkin_day;
  }

  /**
   * {@inheritdoc}
   */
  public function applyConstraint(&$calendar_response) {
    parent::applyConstraint($calendar_response);

    if ($this->start_date->getTimestamp() <= $calendar_response->getStartDate()->getTimestamp() &&
        $this->end_date->getTimestamp() >= $calendar_response->getEndDate()->getTimestamp() && 
        ($this->checkin_day === NULL || $this->checkin_day == $calendar_response->getStartDate()->format('N'))) {

      $units = $this->getUnits();

      $included_set = $calendar_response->getIncluded();

      foreach ($included_set as $unit_id => $set) {
        if (isset($units[$unit_id]) || empty($units)) {
          $start_date = $calendar_response->getStartDate();
          $end_date = $calendar_response->getEndDate();

          $diff = $end_date->diff($start_date)->days;
          if (is_numeric($this->min_days) && $diff < $this->min_days) {
            $calendar_response->removeFromMatched($included_set[$unit_id]['unit'], CalendarResponse::CONSTRAINT, $this);

            $this->affected_units[$unit_id] = $included_set[$unit_id]['unit'];
          }
          elseif (is_numeric($this->max_days) && $diff > $this->max_days) {
            $calendar_response->removeFromMatched($included_set[$unit_id]['unit'], CalendarResponse::CONSTRAINT, $this);

            $this->affected_units[$unit_id] = $included_set[$unit_id]['unit'];
          }
        }
      }
    }
  }

}
