<?php

/**
 * @file
 */

/**
 *
 */
class bat_booking_handler_night_field extends views_handler_field {

  /**
   * {@inheritdoc}
   */
  public function construct() {
    parent::construct();

    $this->additional_fields['booking_id'] = 'booking_id';
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensure_my_table();
    $this->add_additional_fields();
  }

  /**
   * {@inheritdoc}
   */
  public function render($values) {
    $booking = bat_booking_load($values->{$this->aliases['booking_id']});

    $start_date = new DateTime($booking->booking_start_date[LANGUAGE_NONE][0]['value']);
    $end_date = new DateTime($booking->booking_end_date[LANGUAGE_NONE][0]['value']);

    return $end_date->diff($start_date)->days;
  }

}
