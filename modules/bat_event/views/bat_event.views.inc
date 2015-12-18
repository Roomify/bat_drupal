<?php

/**
 * @file
 * Providing extra functionality for the Unit UI via views.
 */

/**
 * Implements hook_views_data_alter().
 */
function bat_event_views_data_alter(&$data) {
  $data['bat_events']['link_event'] = array(
    'field' => array(
      'title' => t('Link'),
      'help' => t('Provide a link to the event.'),
      'handler' => 'bat_event_handler_link_field',
    ),
  );
  $data['bat_events']['edit_event'] = array(
    'field' => array(
      'title' => t('Edit Link'),
      'help' => t('Provide a link to the edit form for the event.'),
      'handler' => 'bat_event_handler_edit_link_field',
    ),
  );
  $data['bat_events']['delete_event'] = array(
    'field' => array(
      'title' => t('Delete Link'),
      'help' => t('Provide a link to delete the event.'),
      'handler' => 'bat_event_handler_delete_link_field',
    ),
  );
  // This content of this field are decided based on the menu structure that
  // follows bat/event/%event_id/op.
  $data['bat_events']['operations'] = array(
    'field' => array(
      'title' => t('Operations links'),
      'help' => t('Display all operations available for this event.'),
      'handler' => 'bat_event_handler_event_operations_field',
    ),
  );
  $data['bat_events']['start_date'] = array(
    'title' => t('Start Date'),
    'help' => t("A event's arrival date."),
    'field' => array(
      'float' => TRUE,
      'handler' => 'bat_event_handler_date_field',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'bat_event_handler_date_filter',
    ),
  );
  $data['bat_events']['end_date'] = array(
    'title' => t('End Date'),
    'help' => t("A event's departure date."),
    'field' => array(
      'float' => TRUE,
      'handler' => 'bat_event_handler_date_field',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'bat_event_handler_date_filter',
    ),
  );
  $data['bat_events']['unit_id'] = array(
    'title' => t('Unit'),
    'help' => t('The unit for which this event was made.'),
    'field' => array(
      'handler' => 'bat_event_handler_unit_field',
    ),
    'filter' => array(
      'title' => t('Unit'),
      'handler' => 'bat_event_handler_unit_filter',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
    'relationship' => array(
      'title' => t('Unit ID'),
      'help' => t("Relate this event to its bookable unit"),
      'handler' => 'views_handler_relationship',
      'base' => 'bat_units',
      'base field' => 'unit_id',
      'field' => 'unit_id',
      'label' => t('Booking unit'),
    ),
  );
  $data['bat_events']['type']['field'] = array(
    'title' => t('Booking Type'),
    'help' => t('The event type label.'),
    'handler' => 'bat_event_handler_event_type_field',
  );

  // Expose the uid as a relationship to users.
  $data['bat_events']['uid'] = array(
    'title' => t('Uid'),
    'help' => t("The owner's user ID."),
    'field' => array(
      'handler' => 'views_handler_field_user',
      'click sortable' => TRUE,
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_user_uid',
      'name field' => 'name',
    ),
    'filter' => array(
      'title' => t('Name'),
      'handler' => 'views_handler_filter_user_name',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
    'relationship' => array(
      'title' => t('Owner'),
      'help' => t("Relate this event to its owner's user account"),
      'handler' => 'views_handler_relationship',
      'base' => 'users',
      'base field' => 'uid',
      'field' => 'uid',
      'label' => t('Booking owner'),
    ),
  );

  $data['bat_events']['event_status']['field']['handler'] = 'bat_event_handler_field_event_status';
  $data['bat_events']['event_status']['filter']['handler'] = 'bat_event_handler_filter_event_status';

  $data['bat_events']['nights'] = array(
    'title' => t('Nights'),
    'help' => t("Booking's number of nights."),
    'field' => array(
      'float' => TRUE,
      'handler' => 'bat_event_handler_nights_field',
      'click sortable' => TRUE,
    ),
  );

}

/**
 * Page callback for views bat_unit autocomplete.
 *
 * @param $units_typed
 *   The typed string of the user.
 */
function bat_event_views_ajax_autocomplete_unit($units_typed = '') {
  // The user enters a comma-separated list of units. We only autocomplete the last unit.
  $units_typed = drupal_explode_tags($units_typed);
  $unit_last = drupal_strtolower(array_pop($units_typed));

  $unit_matches = array();
  if ($unit_last != '') {

    $query = db_select('bat_units', 't');

    // Do not select already entered units.
    if (!empty($units_typed)) {
      $query->condition('t.name', $units_typed, 'NOT IN');
    }
    // Select rows that match by unit name.
    $units_return = $query
      ->fields('t', array('unit_id', 'name'))
      ->condition('t.name', '%' . db_like($unit_last) . '%', 'LIKE')
      ->range(0, 10)
      ->execute()
      ->fetchAllKeyed();

    $prefix = count($units_typed) ? drupal_implode_tags($units_typed) . ', ' : '';

    foreach ($units_return as $unit_id => $name) {
      $unit = bat_unit_load($unit_id);

      if (bat_unit_access('update', $unit)) {
        $n = $name;
        // Unit names containing commas or quotes must be wrapped in quotes.
        if (strpos($name, ',') !== FALSE || strpos($name, '"') !== FALSE) {
          $n = '"' . str_replace('"', '""', $name) . '"';
        }
        // Add unit name to list of matches.
        $unit_matches[$prefix . $n] = check_plain($name);
      }
    }
  }

  drupal_json_output($unit_matches);
}
