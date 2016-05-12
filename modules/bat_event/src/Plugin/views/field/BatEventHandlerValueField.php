<?php

/**
 * @file
 */

namespace Drupal\bat_event\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * @ViewsField("bat_event_handler_value_field")
 */
class BatEventHandlerValueField extends FieldPluginBase {

  function construct() {
    parent::construct();
  }

  function query() {
    $this->field_alias = 'event_id';
  }

  function render($values) {
    $event = bat_event_load($this->get_value($values));
    $event_type = bat_event_type_load($event->type);

    if ($event_type->fixed_event_states) {
      $state = bat_event_load_state($event->event_state_reference[LANGUAGE_NONE][0]['state_id']);

      return $state['label'];
    }
    else {
      $field_name = $event_type->default_event_value_field_ids[$event->type];

      $value = field_get_items('bat_event', $event, $field_name);
      $field_view_value = field_view_value('bat_event', $event, $field_name, $value[0]);

      return $field_view_value['#markup'];
    }
  }

}
