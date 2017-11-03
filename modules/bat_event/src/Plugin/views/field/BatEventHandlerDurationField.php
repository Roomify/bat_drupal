<?php

/**
 * @file
 */

namespace Drupal\bat_event\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * @ViewsField("bat_event_handler_duration_field")
 */
class BatEventHandlerDurationField extends FieldPluginBase {

  public function construct() {
    parent::construct();
  }

  public function query() {
  }

  public function render(ResultRow $values) {
    $event = $this->getEntity($values);

    $value = $event->end->value - $event->start->value;
    $value += 60;

    return $this->sanitizeValue(\Drupal::service('date.formatter')->formatInterval($value));
  }

}
