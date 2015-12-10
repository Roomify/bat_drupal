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
   * @param Drupal\bat_availability\BatEventInterface
   */
	public function saveEvent(BatEventInterface $event);

	/**
   * @param Drupal\bat_availability\BatEventInterface
   */
	public function updateEvent(BatEventInterface $event);

	/**
   * @param Drupal\bat_availability\BatEventInterface
   */
	public function deleteEvent(BatEventInterface $event);
}
