<?php

/**
 * @file
 * Contains UnitPricingCalendarInterface.
 */

namespace Drupal\bat_pricing;

use Drupal\bat\BatCalendarInterface;


/**
 * Handles querying and updating the pricing information
 * relative to a single bookable unit.
 */
interface UnitPricingCalendarInterface extends BatCalendarInterface {

  /**
   * Apply price modifiers to base price.
   *
   * @param float $base_price
   *   The price to modify.
   * @param int $days
   *   The event duration.
   * @param array $reply
   *   A log of prices changes
   *
   * @return float
   *   The modified price.
   */
  public function applyPriceModifiers($base_price, $days, &$reply);

  /**
   * Get a set of PricingEvent between start_date and end_date filtered by days.
   *
   * @param int $unit_id
   *   The unit id to calculate.
   * @param int $amount
   *   The initial amount.
   * @param \DateTime $start_date
   *   The start date.
   * @param \DateTime $end_date
   *   The end date.
   * @param string $operation
   *   The operation to perform.
   * @param int $days
   *   The event duration.
   * 
   * @return PricingEventInterface[]
   *   The events in that range of dates
   */
  public function calculatePricingEvents($unit_id, $amount, \DateTime $start_date, \DateTime $end_date, $operation, $days);

  /**
   * Given a date range determine the cost of the room over that period.
   *
   * @param \DateTime $start_date
   *   The starting date for the search.
   * @param \DateTime $end_date
   *   The end date for the search.
   * @param int $persons
   *   The number of persons staying in this room.
   * @param int $children
   *   The number of children staying in this room.
   * @param array $children_ages
   *   Children ages.
   *
   * @return array
   *   Array holding full price and booking price of the room for that period.
   */
  public function calculatePrice(\DateTime $start_date, \DateTime $end_date, $persons = 0, $children = 0, $children_ages = array());

}
