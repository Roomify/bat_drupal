<?php

/**
 * @file
 * This field handler aggregates calendar edit links for a Bat Type
 * under a single field.
 */

class bat_type_handler_type_calendars_field extends views_handler_field {
  function construct() {
    parent::construct();

    $this->additional_fields['type_id'] = 'type_id';
  }

  function query() {
    $this->ensure_my_table();
    $this->add_additional_fields();
  }

  function render($values) {
    $links = array();
    $type = bat_type_load($values->type_id);
    $type_bundle = bat_type_bundle_load($type->type);
    if (is_array($type_bundle->data)) {
      foreach ($type_bundle->data as $event => $field) {
        if (!empty($field)) {
          $links[$field] = array(
            'title' => 'Manage ' . $event,
            // TODO: link to filtered calendar.
            'href' => 'admin/bat/calendar',
          );
        }
      }
    }

    if (!empty($links)) {
      return theme('links', array(
        'links' => $links,
        'attributes' => array(
          'class' => array(
            'links',
            'inline',
            'calendars',
          ),
        ),
      ));
    }
  }
}
