<?php

/**
 * @file
 * This field handler aggregates calendar edit links for a Bat Type
 * under a single field.
 */

namespace Drupal\bat_booking\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\Core\Url;

/**
 * @ViewsField("bat_booking_handler_night_field")
 */
class BatBookingHandlerNightField extends FieldPluginBase {

  public function construct() {
    parent::construct();

    $this->additional_fields['booking_id'] = 'booking_id';
  }

  public function query() {
    $this->ensure_my_table();
    $this->add_additional_fields();
  }

  public function render($values) {
    $booking = bat_booking_load($values->{$this->aliases['booking_id']});

    $start_date = new DateTime($booking->booking_start_date[LANGUAGE_NONE][0]['value']);
    $end_date = new DateTime($booking->booking_end_date[LANGUAGE_NONE][0]['value']);

    return $end_date->diff($start_date)->days;
  }

}
