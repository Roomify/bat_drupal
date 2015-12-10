<?php

/**
 * @file
 * Contains \Drupal\bat_availability\BatCalendar.
 */

namespace Drupal\bat_availability;

/**
 *
 */
class BatCalendar implements BatCalendarInterface {
  /**
   *
   */
  private $unit_id;

  /**
   *
   */
  private $controller;

  /**
   *
   */
  public function __construct($unit_id, $controller) {
    $this->unit_id = $unit_id;
    $this->controller = $controller;
  }

  /**
   *
   */
  public function getEvents(\DateTime $start_date, \DateTime $end_date) {

  }

  /**
   *
   */
  public function addEvents(array $events) {
    foreach ($events as $event) {
      $this->controller->saveEvent($event);
    }
  }

  /**
   *
   */
  public function deleteEvents(array $events) {
   foreach ($events as $event) {
      $this->controller->deleteEvent($event);
    } 
  }

  /**
   *
   */
  public function updateEvents(array $events) {
    foreach ($events as $event) {
      $this->controller->updateEvent($event);
    }
  }
}
