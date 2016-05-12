<?php

/**
 * @file
 *
 * Field handlers for BAT units.
 */


/**
 * Implements hook_views_data_alter().
 */
function bat_unit_views_data_alter(array &$data) {
  // Views alterations for Bat Units.
  $data['unit']['type']['field'] = array(
    'title' => t('Booking Unit Bundle'),
    'help' => t('Booking Unit Bundle Label.'),
    'handler' => 'bat_unit_handler_unit_bundle_field',
  );

  // Expose the uid as a relationship to users.
  $data['unit']['uid'] = array(
    'title' => t('Uid'),
    'help' => t("The owner's user ID."),
    'field' => array(
      'id' => 'views_handler_field_user',
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
      'help' => t("Relate this unit to its owner's user account"),
      'handler' => 'views_handler_relationship',
      'base' => 'users',
      'base field' => 'uid',
      'field' => 'uid',
      'label' => t('Unit owner'),
    ),
  );

  $data['unit']['type_id']['filter']['id'] = 'bat_unit_handler_type_id_filter';
  $data['unit']['type_id']['field']['id'] = 'bat_unit_handler_type_id_field';

  $data['unit']['type_id']['relationship'] = array(
    'title' => t('Type'),
    'help' => t('Relate this unit its type'),
    'handler' => 'views_handler_relationship',
    'base' => 'bat_types',
    'base field' => 'type_id',
    'field' => 'type_id',
    'label' => t('Type'),
  );

  $data['unit_type']['calendars'] = array(
    'field' => array(
      'title' => t('Event Management'),
      'help' => t('Display links to manage all calendars for this Type.'),
      'id' => 'bat_type_handler_type_calendars_field',
    ),
  );
}
