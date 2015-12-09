<?php

/**
 * @file
 * Contains \Drupal\bat_availability\BatAgentInterface.
 */

namespace Drupal\bat_availability;

use Drupal\bat_availability\BatAgentInterface;
use Drupal\bat_availability\BatCalendarController;
use Drupal\bat_availability\BatCalendar;
use Drupal\bat_availability\BatEvent;

/**
 *
 */
class BatAgent implements BatAgentInterface {
  /**
   *
   */
  private $unit_id;

  /**
   *
   */
  private $availability_states;

  /**
   *
   */
  public function __constructor($unit_id) {
    $this->unit_id = $unit_id;
  }

  /**
   *
   */
  public function setValidStates(array $availability_states) {
    $this->availability_states = $availability_states;
  }

  /**
   *
   */
  public function getAvailability(array $availability_filters) {
    return $this->availability_states;
  }

  /**
   *
   */
  public function checkAvailability() {

  }

  /**
   *
   */
  public function updateAvailabilityStates(\DateTime $start_date, \DateTime $end_date, $state) {
    $controller = new BatCalendarController();
    $calendar = new BatCalendar($this->unit_id, $controller);
    $event = new BatEvent($start_date, $end_date, $state);

    $calendar->addEvents(array($event));
  }

  /**
   *
   */
  public function updateAvailabilityEvents($availability_event_entity) {

  }
}
