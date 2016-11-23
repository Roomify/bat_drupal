<?php

/**
 * @file
 * This field handler aggregates calendar edit links for a Bat Type
 * under a single field.
 */

/**
 *
 */
class bat_type_handler_type_calendars_field extends views_handler_field {

  /**
   * {@inheritdoc}
   */
  public function construct() {
    parent::construct();

    $this->additional_fields['type_id'] = 'type_id';
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
    $links = array();

    $type = bat_type_load($this->get_value($values, 'type_id'));
    $type_bundle = bat_type_bundle_load($type->type);
    if (is_array($type_bundle->default_event_value_field_ids)) {
      foreach ($type_bundle->default_event_value_field_ids as $event_type => $field) {
        if (!empty($field)) {
          $event_type_path = 'admin/bat/calendar/' . $type->type_id . '/' . $event_type;

          // Check if user has permission to access $event_type_path.
          if (drupal_valid_path($event_type_path)) {
            $event_type_label = bat_event_get_types($event_type)->label;
            $links[$field] = array(
              'title' => 'Manage ' . $event_type_label,
              'href' => $event_type_path,
            );
          }
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
    else {
      // Hide this field.
      $this->options['exclude'] = TRUE;
    }
  }

}
