<?php

/**
 * @file
 * Contains \Drupal\bat_availability\BatCalendarControllerInterface.
 */

namespace Drupal\bat_availability;

/**
 *
 */
interface BatCalendarControllerInterface {
	/**
   *
   */
	public function saveEvent();

	/**
   *
   */
	public function updateEvent();

	/**
   *
   */
	public function deleteEvent();
}
