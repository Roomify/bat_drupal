<?php

/**
 * @file
 * A pricing event represent a price over a set of continuous dates. As soon as
 * the price changes that is a different pricing event
 */

namespace Drupal\bat_pricing;

use Drupal\bat\BatEventInterface;


interface PricingEventInterface extends BatEventInterface {
  /**
   * Applies an operation against a Price event.
   *
   * @param int $amount
   *   The operation amount.
   * @param string $operation
   *   The operation type.
   * @param int $days
   *   The number of days the event lasts.
   */
  public function applyOperation($amount, $operation);

  /**
   * Returns event in a format amenable to FullCalendar display or generally
   * sensible json.
   *
   * @return array
   *   The processed event, in JSON ready format.
   */
  public function formatJson();

}
