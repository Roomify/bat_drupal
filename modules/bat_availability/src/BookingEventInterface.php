<?php

/**
 * @file
 * Interface BookingEventInterface
 */

namespace Drupal\bat_availability;

use Drupal\bat\BatEventInterface;

interface BookingEventInterface extends BatEventInterface {

  /**
   * Locks event - updates the bat_booking_locks table to indicate that this
   * event is locked.
   *
   * @return bool
   *   TRUE if the event is locked successfully, FALSE if was already blocked.
   */
  public function lock();

  /**
   * Unlocks the event.
   */
  public function unlock();

  /**
   * Returns event in a format amenable to FullCalendar display or generally
   * sensible JSON.
   *
   * @param int $style
   *   The visualization style.
   * @param string $unit_name
   *   The bookable unit name.
   *
   * @return array
   *   The processed event, in JSON ready format.
   */
  public function formatJson($style = BAT_AVAILABILITY_ADMIN_STYLE, $unit_name = '');

}
