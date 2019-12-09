<?php

/**
 * @file
 * This field handler aggregates calendar edit links for a Bat Type
 * under a single field.
 */

namespace Drupal\bat_booking\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * @ViewsField("bat_booking_handler_night_field")
 */
class BatBookingHandlerNightField extends FieldPluginBase {

  public function query() {
  }

  public function render(ResultRow $values) {
    $booking = $this->getEntity($values);

    $start_date = new \DateTime($booking->get('booking_start_date')->getValue()[0]['value']);
    $end_date = new \DateTime($booking->get('booking_end_date')->getValue()[0]['value']);

    return $end_date->diff($start_date)->days;
  }

}
