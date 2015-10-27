<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying the correct label
 * for unit types.
 */


class bat_booking_handler_booking_type_field extends views_handler_field {
  function construct() {
    parent::construct();
  }

  function render($values) {
    $booking_type = bat_booking_type_load($values->bat_bookings_type);
    return $booking_type->label;
  }

}
