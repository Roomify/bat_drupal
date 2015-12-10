<?php

/**
 * @file
 * Contains \Drupal\bat_availability\BatCalendarControllerInterface.
 */

namespace Drupal\bat_availability;

use Drupal\bat_availability\BatEventInterface;

/**
 *
 */
interface BatCalendarControllerInterface {
	/**
   *
   */
	public function saveEvent(BatEventInterface $event);

	/**
   *
   */
	public function updateEvent(BatEventInterface $event);

	/**
   *
   */
	public function deleteEvent(BatEventInterface $event);
}
