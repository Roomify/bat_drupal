<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying booking status options.
 */

class bat_booking_handler_field_booking_status extends views_handler_field {
  function render($values) {
    if ($values->bat_bookings_booking_status == 0) return 'Unconfirmed';
    elseif ($values->bat_bookings_booking_status == 1) return 'Confirmed';

    return '';
  }
}
