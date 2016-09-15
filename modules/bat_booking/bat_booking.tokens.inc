<?php

/**
 * @file
 * Builds placeholder replacement tokens for booking-related data.
 */

/**
 * Implements hook_token_info().
 */
function bat_booking_token_info() {
  $type = array(
    'name' => t('BAT Booking', array(), array('context' => 'a bat booking')),
    'description' => t('Tokens related to bat booking.'),
    'needs-data' => 'bat-booking',
  );

  return array(
    'types' => array('bat_booking' => $type),
    'tokens' => array('bat_booking' => array()),
  );
}
