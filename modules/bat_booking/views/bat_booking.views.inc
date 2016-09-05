<?php

/**
 * @file
 */

/**
 * Implements hook_views_data_alter().
 */
function bat_booking_views_data_alter(&$data) {
  $data['bat_bookings']['edit_unit'] = array(
    'field' => array(
      'title' => t('Edit Link'),
      'help' => t('Provide a link to the edit form for the booking.'),
      'handler' => 'bat_booking_handler_edit_link_field',
    ),
  );
  $data['bat_bookings']['delete_unit'] = array(
    'field' => array(
      'title' => t('Delete Link'),
      'help' => t('Provide a link to delete the booking.'),
      'handler' => 'bat_booking_handler_delete_link_field',
    ),
  );

  $data['bat_bookings']['nights'] = array(
    'field' => array(
      'title' => t('Nights'),
      'help' => t('Provide number of nights.'),
      'handler' => 'bat_booking_handler_night_field',
    ),
  );
}
