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
 * Implements hook_views_default_views().
 */
function bat_booking_views_default_views() {
  $views = array();

  $view = new view();
  $view->name = 'bookings';
  $view->description = 'A list of all bookings';
  $view->tag = 'bat';
  $view->base_table = 'bat_bookings';
  $view->human_name = 'Bookings';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Bookings';
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'perm';
  $handler->display->display_options['access']['perm'] = 'bypass bat_booking entities access';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['query']['options']['query_comment'] = FALSE;
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'booking_id' => 'booking_id',
    'name' => 'name',
    'start_date' => 'start_date',
    'end_date' => 'end_date',
    'unit_id' => 'unit_id',
    'type' => 'type',
    'name_1' => 'name_1',
    'operations' => 'operations',
  );
  $handler->display->display_options['style_options']['default'] = 'booking_id';
  $handler->display->display_options['style_options']['info'] = array(
    'booking_id' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'start_date' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'end_date' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'unit_id' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'type' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'name_1' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'operations' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
  );
  /* No results behavior: Global: Text area */
  $handler->display->display_options['empty']['area']['id'] = 'area';
  $handler->display->display_options['empty']['area']['table'] = 'views';
  $handler->display->display_options['empty']['area']['field'] = 'area';
  $handler->display->display_options['empty']['area']['label'] = 'Empty Message';
  $handler->display->display_options['empty']['area']['content'] = 'No bookings have been created yet.';
  $handler->display->display_options['empty']['area']['format'] = 'filtered_html';
  /* Relationship: Bookings: Owner */
  $handler->display->display_options['relationships']['uid']['id'] = 'uid';
  $handler->display->display_options['relationships']['uid']['table'] = 'bat_bookings';
  $handler->display->display_options['relationships']['uid']['field'] = 'uid';
  $handler->display->display_options['relationships']['uid']['required'] = TRUE;
  /* Field: Bookings: Bookings ID */
  $handler->display->display_options['fields']['booking_id']['id'] = 'booking_id';
  $handler->display->display_options['fields']['booking_id']['table'] = 'bat_bookings';
  $handler->display->display_options['fields']['booking_id']['field'] = 'booking_id';
  $handler->display->display_options['fields']['booking_id']['label'] = 'Booking';
  /* Field: Bookings: Start Date */
  $handler->display->display_options['fields']['start_date']['id'] = 'start_date';
  $handler->display->display_options['fields']['start_date']['table'] = 'bat_bookings';
  $handler->display->display_options['fields']['start_date']['field'] = 'start_date';
  $handler->display->display_options['fields']['start_date']['label'] = 'Arrival';
  $handler->display->display_options['fields']['start_date']['hide_alter_empty'] = FALSE;
  $handler->display->display_options['fields']['start_date']['date_format'] = 'custom';
  $handler->display->display_options['fields']['start_date']['custom_date_format'] = 'd-m-Y';
  /* Field: Bookings: End Date */
  $handler->display->display_options['fields']['end_date']['id'] = 'end_date';
  $handler->display->display_options['fields']['end_date']['table'] = 'bat_bookings';
  $handler->display->display_options['fields']['end_date']['field'] = 'end_date';
  $handler->display->display_options['fields']['end_date']['label'] = 'Departure';
  $handler->display->display_options['fields']['end_date']['hide_alter_empty'] = FALSE;
  $handler->display->display_options['fields']['end_date']['date_format'] = 'custom';
  $handler->display->display_options['fields']['end_date']['custom_date_format'] = 'd-m-Y';
  /* Field: Bookings: Unit */
  $handler->display->display_options['fields']['unit_id']['id'] = 'unit_id';
  $handler->display->display_options['fields']['unit_id']['table'] = 'bat_bookings';
  $handler->display->display_options['fields']['unit_id']['field'] = 'unit_id';
  $handler->display->display_options['fields']['unit_id']['hide_alter_empty'] = FALSE;
  /* Field: Bookings: Booking Type */
  $handler->display->display_options['fields']['type']['id'] = 'type';
  $handler->display->display_options['fields']['type']['table'] = 'bat_bookings';
  $handler->display->display_options['fields']['type']['field'] = 'type';
  $handler->display->display_options['fields']['type']['label'] = 'Type';
  $handler->display->display_options['fields']['type']['hide_alter_empty'] = FALSE;
  /* Field: User: Name */
  $handler->display->display_options['fields']['name_1']['id'] = 'name_1';
  $handler->display->display_options['fields']['name_1']['table'] = 'users';
  $handler->display->display_options['fields']['name_1']['field'] = 'name';
  $handler->display->display_options['fields']['name_1']['relationship'] = 'uid';
  $handler->display->display_options['fields']['name_1']['label'] = 'Owner';
  /* Field: Bookings: Operations links */
  $handler->display->display_options['fields']['operations']['id'] = 'operations';
  $handler->display->display_options['fields']['operations']['table'] = 'bat_bookings';
  $handler->display->display_options['fields']['operations']['field'] = 'operations';
  $handler->display->display_options['fields']['operations']['hide_alter_empty'] = FALSE;
  /* Filter criterion: Bookings: Bookings ID */
  $handler->display->display_options['filters']['booking_id']['id'] = 'booking_id';
  $handler->display->display_options['filters']['booking_id']['table'] = 'bat_bookings';
  $handler->display->display_options['filters']['booking_id']['field'] = 'booking_id';
  $handler->display->display_options['filters']['booking_id']['group'] = 1;
  $handler->display->display_options['filters']['booking_id']['exposed'] = TRUE;
  $handler->display->display_options['filters']['booking_id']['expose']['operator_id'] = 'booking_id_op';
  $handler->display->display_options['filters']['booking_id']['expose']['label'] = 'Booking ID';
  $handler->display->display_options['filters']['booking_id']['expose']['operator'] = 'booking_id_op';
  $handler->display->display_options['filters']['booking_id']['expose']['identifier'] = 'booking_id';
  $handler->display->display_options['filters']['booking_id']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    3 => 0,
  );
  /* Filter criterion: Bookings: Unit */
  $handler->display->display_options['filters']['unit_id']['id'] = 'unit_id';
  $handler->display->display_options['filters']['unit_id']['table'] = 'bat_bookings';
  $handler->display->display_options['filters']['unit_id']['field'] = 'unit_id';
  $handler->display->display_options['filters']['unit_id']['value'] = '';
  $handler->display->display_options['filters']['unit_id']['group'] = 1;
  $handler->display->display_options['filters']['unit_id']['exposed'] = TRUE;
  $handler->display->display_options['filters']['unit_id']['expose']['operator_id'] = 'unit_id_op';
  $handler->display->display_options['filters']['unit_id']['expose']['label'] = 'Unit';
  $handler->display->display_options['filters']['unit_id']['expose']['operator'] = 'unit_id_op';
  $handler->display->display_options['filters']['unit_id']['expose']['identifier'] = 'unit_id';
  $handler->display->display_options['filters']['unit_id']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    3 => 0,
  );
  /* Filter criterion: User: Name */
  $handler->display->display_options['filters']['uid']['id'] = 'uid';
  $handler->display->display_options['filters']['uid']['table'] = 'users';
  $handler->display->display_options['filters']['uid']['field'] = 'uid';
  $handler->display->display_options['filters']['uid']['relationship'] = 'uid';
  $handler->display->display_options['filters']['uid']['value'] = '';
  $handler->display->display_options['filters']['uid']['group'] = 1;
  $handler->display->display_options['filters']['uid']['exposed'] = TRUE;
  $handler->display->display_options['filters']['uid']['expose']['operator_id'] = 'uid_op';
  $handler->display->display_options['filters']['uid']['expose']['label'] = 'Owner';
  $handler->display->display_options['filters']['uid']['expose']['operator'] = 'uid_op';
  $handler->display->display_options['filters']['uid']['expose']['identifier'] = 'uid';
  $handler->display->display_options['filters']['uid']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    3 => 0,
  );
  /* Filter criterion: Bookings: Start Date */
  $handler->display->display_options['filters']['start_date']['id'] = 'start_date';
  $handler->display->display_options['filters']['start_date']['table'] = 'bat_bookings';
  $handler->display->display_options['filters']['start_date']['field'] = 'start_date';
  $handler->display->display_options['filters']['start_date']['operator'] = '>=';
  $handler->display->display_options['filters']['start_date']['group'] = 1;
  $handler->display->display_options['filters']['start_date']['exposed'] = TRUE;
  $handler->display->display_options['filters']['start_date']['expose']['operator_id'] = 'start_date_op';
  $handler->display->display_options['filters']['start_date']['expose']['label'] = 'Arriving after';
  $handler->display->display_options['filters']['start_date']['expose']['operator'] = 'start_date_op';
  $handler->display->display_options['filters']['start_date']['expose']['identifier'] = 'start_date';
  $handler->display->display_options['filters']['start_date']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    3 => 0,
  );
  /* Filter criterion: Bookings: End Date */
  $handler->display->display_options['filters']['end_date']['id'] = 'end_date';
  $handler->display->display_options['filters']['end_date']['table'] = 'bat_bookings';
  $handler->display->display_options['filters']['end_date']['field'] = 'end_date';
  $handler->display->display_options['filters']['end_date']['operator'] = '>=';
  $handler->display->display_options['filters']['end_date']['group'] = 1;
  $handler->display->display_options['filters']['end_date']['exposed'] = TRUE;
  $handler->display->display_options['filters']['end_date']['expose']['operator_id'] = 'end_date_op';
  $handler->display->display_options['filters']['end_date']['expose']['label'] = 'Departing after';
  $handler->display->display_options['filters']['end_date']['expose']['operator'] = 'end_date_op';
  $handler->display->display_options['filters']['end_date']['expose']['identifier'] = 'end_date';
  $handler->display->display_options['filters']['end_date']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    3 => 0,
  );

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page_1');
  $handler->display->display_options['path'] = 'admin/bat/bookings/list';
  $handler->display->display_options['menu']['type'] = 'default tab';
  $handler->display->display_options['menu']['title'] = 'Bookings List';
  $handler->display->display_options['menu']['description'] = 'List of bookings';
  $handler->display->display_options['menu']['weight'] = '-10';
  $handler->display->display_options['tab_options']['type'] = 'normal';
  $handler->display->display_options['tab_options']['title'] = 'Bookings';
  $handler->display->display_options['tab_options']['description'] = 'A list of all bookings. Bookings can be created, edited or deleted manually or can be created following a purchase from a client using Drupal Commerce.';
  $handler->display->display_options['tab_options']['weight'] = '0';
  $handler->display->display_options['tab_options']['name'] = 'management';

  $views['bookings'] = $view;
  return $views;
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
