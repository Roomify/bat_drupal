<?php

/**
 * @file
 * This file contains no working PHP code; it exists to provide additional
 * documentation for doxygen as well as to document hooks in the standard
 * Drupal manner.
 */

/**
 * Allows to modify the price modifiers array for a booking.
 *
 * @param array $price_modifiers
 *   Array containing the booking price modifiers.
 * @param array $booking_info
 *   Array containing the booking information. Contains the following key/value
 *   pairs:
 *   - start_date: DateTime object containing the booking start date.
 *   - end_date: DateTime object containing the booking end date. In this case,
 *   the end date represents the last night the unit is blocked, so it is one
 *   day before the checkout date entered in the booking form.
 *   - unit: The BatUnit entity the booking is related to.
 *   - booking_parameters: Array containing some other booking parameters:
 *     - group_size: The total number of persons included in the booking.
 *     - group_size_children: The number of children.
 *     - childrens_age: Array containing children's age.
 */
function hook_bat_price_modifier_alter(&$price_modifiers, $booking_info) {
  // This adds programmatically a 10$ discount.
  $price_modifiers['my_module'] = array(
    '#type' => BAT_DYNAMIC_MODIFIER,
    '#quantity' => 1,
    '#op_type' => BAT_SUB,
    '#amount' => 10
  );
}
