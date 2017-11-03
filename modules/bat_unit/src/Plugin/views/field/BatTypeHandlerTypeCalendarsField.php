<?php

/**
 * @file
 * This field handler aggregates calendar edit links for a Bat Type
 * under a single field.
 */

namespace Drupal\bat_unit\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\Core\Url;

/**
 * @ViewsField("bat_type_handler_type_calendars_field")
 */
class BatTypeHandlerTypeCalendarsField extends FieldPluginBase {

  public function construct() {
    parent::construct();
  }

  public function query() {
  }

  public function render(ResultRow $values) {
    $links = [];

    $type = $this->getEntity($values);
    $type_bundle = bat_type_bundle_load($type->bundle());

    if (is_array($type_bundle->default_event_value_field_ids)) {
      foreach ($type_bundle->default_event_value_field_ids as $event_type => $field) {
        if (!empty($field)) {
          $event_type_path = 'admin/bat/calendar/' . $type->id() . '/' . $event_type;

          // Check if user has permission to access $event_type_path.
          if ($url_object = \Drupal::service('path.validator')->getUrlIfValid($event_type_path)) {
            $route_name = $url_object->getRouteName();

            if (bat_event_get_types($event_type)) {
              $event_type_label = bat_event_get_types($event_type)->label();
              $links[$event_type] = [
                'title' => t('Manage @event_type_label', ['@event_type_label' => $event_type_label]),
                'url' => Url::fromRoute($route_name, ['unit_type' => $type->id(), 'event_type' => $event_type]),
              ];
            }
          }
        }
      }
    }

    if (!empty($links)) {
      return [
        '#type' => 'operations',
        '#links' => $links,
      ];
    }
    else {
      // Hide this field.
      $this->options['exclude'] = TRUE;
    }
  }

}
