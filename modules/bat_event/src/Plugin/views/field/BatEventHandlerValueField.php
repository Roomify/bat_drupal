<?php

/**
 * @file
 */

namespace Drupal\bat_event\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * @ViewsField("bat_event_handler_value_field")
 */
class BatEventHandlerValueField extends FieldPluginBase {

  public function query() {
    $this->field_alias = 'event_id';
  }

  public function render(ResultRow $values) {
    $event = $this->getEntity($values);
    $event_type = bat_event_type_load($event->bundle());

    if ($event_type->getFixedEventStates()) {
      $state = $event->get('event_state_reference')->entity;

      return $state->label();
    }
    else {
      $field_name = $event_type->default_event_value_field_ids;

      $elements = $event->{$field_name}->view(['label' => 'hidden']);
      $value = $this->getRenderer()->render($elements);

      return $value;
    }
  }

}
