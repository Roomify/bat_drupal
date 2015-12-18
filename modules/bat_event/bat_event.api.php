<?php

/**
 * @file
 * This file contains no working PHP code; it exists to provide additional
 * documentation for doxygen as well as to document hooks in the standard
 * Drupal manner.
 */

/**
 * Allows to modify the price modifiers array for a event.
 *
 * @param array $price_modifiers
 *   Array containing the event price modifiers.
 * @param array $event_info
 *   Array containing the event information. Contains the following key/value
 *   pairs:
 *   - start_date: DateTime object containing the event start date.
 *   - end_date: DateTime object containing the event end date. In this case,
 *   the end date represents the last night the unit is blocked, so it is one
 *   day before the checkout date entered in the event form.
 *   - unit: The BatUnit entity the event is related to.
 *   - event_parameters: Array containing some other event parameters:
 *     - group_size: The total number of persons included in the event.
 */
function hook_bat_price_modifier_alter(&$price_modifiers, $event_info) {
  // This adds programmatically a 10$ discount.
  $price_modifiers['my_module'] = array(
    '#type' => BAT_DYNAMIC_MODIFIER,
    '#quantity' => 1,
    '#op_type' => BAT_SUB,
    '#amount' => 10
  );
}
