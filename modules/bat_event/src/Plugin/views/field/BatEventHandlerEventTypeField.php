<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying the correct label
 * for event bundles.
 */

namespace Drupal\bat_event\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * @ViewsField("bat_event_handler_event_type_field")
 */
class BatEventHandlerEventTypeField extends FieldPluginBase {

  public function construct() {
    parent::construct();
  }

  public function render(ResultRow $values) {
    $event_type = bat_event_type_load($this->get_value($values));
    return $event_type->label;
  }

}
