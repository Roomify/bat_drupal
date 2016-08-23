<?php


/**
 * Implements hook_views_data_alter().
 */
function bat_booking_views_data_alter(&$data) {
  $data['bat_bookings']['nights'] = array(
    'field' => array(
      'title' => t('Nights'),
      'help' => t('Provide number of nights.'),
      'handler' => 'bat_booking_handler_night_field',
    ),
  );
}
