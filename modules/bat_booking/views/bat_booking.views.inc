<?php

/**
 * @file
 * Providing extra functionality for the Unit UI via views.
 */

/**
 * Implements hook_views_data_alter().
 */
function bat_booking_views_data_alter(&$data) {
  $data['bat_bookings']['link_booking'] = array(
    'field' => array(
      'title' => t('Link'),
      'help' => t('Provide a link to the booking.'),
      'handler' => 'bat_booking_handler_link_field',
    ),
  );
  $data['bat_bookings']['edit_booking'] = array(
    'field' => array(
      'title' => t('Edit Link'),
      'help' => t('Provide a link to the edit form for the booking.'),
      'handler' => 'bat_booking_handler_edit_link_field',
    ),
  );
  $data['bat_bookings']['delete_booking'] = array(
    'field' => array(
      'title' => t('Delete Link'),
      'help' => t('Provide a link to delete the booking.'),
      'handler' => 'bat_booking_handler_delete_link_field',
    ),
  );
  // This content of this field are decided based on the menu structure that
  // follows bat/booking/%booking_id/op.
  $data['bat_bookings']['operations'] = array(
    'field' => array(
      'title' => t('Operations links'),
      'help' => t('Display all operations available for this booking.'),
      'handler' => 'bat_booking_handler_booking_operations_field',
    ),
  );
  $data['bat_bookings']['start_date'] = array(
    'title' => t('Start Date'),
    'help' => t("A booking's arrival date."),
    'field' => array(
      'float' => TRUE,
      'handler' => 'bat_booking_handler_date_field',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'bat_booking_handler_date_filter',
    ),
  );
  $data['bat_bookings']['end_date'] = array(
    'title' => t('End Date'),
    'help' => t("A booking's departure date."),
    'field' => array(
      'float' => TRUE,
      'handler' => 'bat_booking_handler_date_field',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'bat_booking_handler_date_filter',
    ),
  );
  $data['bat_bookings']['unit_id'] = array(
    'title' => t('Unit'),
    'help' => t('The unit for which this booking was made.'),
    'field' => array(
      'handler' => 'bat_booking_handler_unit_field',
    ),
    'filter' => array(
      'title' => t('Unit'),
      'handler' => 'bat_booking_handler_unit_filter',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
    'relationship' => array(
      'title' => t('Unit ID'),
      'help' => t("Relate this booking to its bookable unit"),
      'handler' => 'views_handler_relationship',
      'base' => 'bat_units',
      'base field' => 'unit_id',
      'field' => 'unit_id',
      'label' => t('Booking unit'),
    ),
  );
  $data['bat_bookings']['type']['field'] = array(
    'title' => t('Booking Type'),
    'help' => t('The booking type label.'),
    'handler' => 'bat_booking_handler_booking_type_field',
  );

  // Expose the uid as a relationship to users.
  $data['bat_bookings']['uid'] = array(
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
      'help' => t("Relate this booking to its owner's user account"),
      'handler' => 'views_handler_relationship',
      'base' => 'users',
      'base field' => 'uid',
      'field' => 'uid',
      'label' => t('Booking owner'),
    ),
  );

  $data['bat_bookings']['booking_status']['field']['handler'] = 'bat_booking_handler_field_booking_status';
  $data['bat_bookings']['booking_status']['filter']['handler'] = 'bat_booking_handler_filter_booking_status';

  $data['bat_bookings']['nights'] = array(
    'title' => t('Nights'),
    'help' => t("Booking's number of nights."),
    'field' => array(
      'float' => TRUE,
      'handler' => 'bat_booking_handler_nights_field',
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
function bat_booking_views_ajax_autocomplete_unit($units_typed = '') {
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
