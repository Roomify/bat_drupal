<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying the correct label
 * for event bundles.
 */

namespace Drupal\bat_event\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;

class BatEventHandlerEventTypeField extends FieldPluginBase {

  function construct() {
    parent::construct();
  }

  function render($values) {
    $event_type = bat_event_type_load($this->get_value($values));
    return $event_type->label;
  }

}
