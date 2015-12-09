<?php

/**
 * @file
 * Contains \Drupal\bat_availability\BatEvent.
 */

namespace Drupal\bat_availability;

/**
 *
 */
class BatEvent implements BatEventInterface {
  /**
   *
   */
  private $start_date;

  /**
   *
   */
  private $end_date;

  /**
   *
   */
  private $state;

  /**
   *
   */
  private $event_id;

  /**
   *
   */
  public function getStartDate() {
    return $this->start_date;
  }

  /**
   *
   */
  public function getEndDate() {
    return $this->end_date;
  }

  /**
   *
   */
  public function setStartDate(\DateTime $start_date) {
    $this->start_date = $start_date;
  }

  /**
   *
   */
  public function setEndDate(\DateTime $end_date) {
    $this->end_date = $end_date;
  }

  /**
   *
   */
  public function getState() {
    $this->state;
  }

  /**
   *
   */
  public function setState($state) {
    $this->state = $state;
  }

  /**
   *
   */
  public function getEventId() {
    return $event_id;
  }
}
