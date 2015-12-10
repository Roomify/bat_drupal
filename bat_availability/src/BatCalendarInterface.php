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
   * @param \DateTime
   * @param \DateTime
   *
   * @return array
   */
  public function getEvents(\DateTime $start_date, \DateTime $end_date);

  /**
   * @param array
   */
  public function addEvents(array $events);

  /**
   * @param array
   */
  public function deleteEvents(array $events);

  /**
   * @param array
   */
  public function updateEvents(array $events);
}
