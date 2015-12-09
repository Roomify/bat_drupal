<?php

/**
 * @file
 * Contains \Drupal\bat_availability\BatCalendarInterface.
 */

namespace Drupal\bat_availability;

/**
 *
 */
interface BatCalendarInterface {
  /**
   *
   */
  public function getEvents(\DateTime $start_date, \DateTime $end_date);

  /**
   *
   */
  public function addEvents(array $events);

  /**
   *
   */
  public function deleteEvents(array $events);

  /**
   *
   */
  public function updateEvents(array $events);
}
