<?php

/**
 * @file
 *
 * Field handlers for BAT units.
 */


/**
 * Implements hook_views_data_alter().
 */
function bat_unit_views_data_alter(&$data) {
  // Views alterations for Bat Units.
  $data['bat_units']['type']['field'] = array(
    'title' => t('Booking Unit Bundle'),
    'help' => t('Booking Unit Bundle Label.'),
    'handler' => 'bat_unit_handler_unit_bundle_field',
  );

  $data['bat_units']['link_unit'] = array(
    'field' => array(
      'title' => t('Link'),
      'help' => t('Provide a link to the unit.'),
      'handler' => 'bat_unit_handler_link_field',
    ),
  );
  $data['bat_units']['edit_unit'] = array(
    'field' => array(
      'title' => t('Edit Link'),
      'help' => t('Provide a link to the edit form for the unit.'),
      'handler' => 'bat_unit_handler_edit_link_field',
    ),
  );
  $data['bat_units']['delete_unit'] = array(
    'field' => array(
      'title' => t('Delete Link'),
      'help' => t('Provide a link to delete the unit.'),
      'handler' => 'bat_unit_handler_delete_link_field',
    ),
  );
  // The content of this field is decided based on the menu structure
  // under bat/unit/%unit_id/op
  $data['bat_units']['operations'] = array(
    'field' => array(
      'title' => t('Operations links'),
      'help' => t('Display all operations available for this unit.'),
      'handler' => 'bat_unit_handler_unit_operations_field',
    ),
  );

  // Expose the uid as a relationship to users.
  $data['bat_units']['uid'] = array(
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
      'help' => t("Relate this unit to its owner's user account"),
      'handler' => 'views_handler_relationship',
      'base' => 'users',
      'base field' => 'uid',
      'field' => 'uid',
      'label' => t('Unit owner'),
    ),
  );

  $data['bat_units']['type_id']['filter']['handler'] = 'bat_unit_handler_type_id_filter';
  $data['bat_units']['type_id']['field']['handler'] = 'bat_unit_handler_type_id_field';

  $data['bat_units']['type_id']['relationship'] = array(
    'title' => t('Type'),
    'help' => t('Relate this unit its type'),
    'handler' => 'views_handler_relationship',
    'base' => 'bat_types',
    'base field' => 'type_id',
    'field' => 'type_id',
    'label' => t('Type'),
  );

  // Views alterations for Bat Types.
  $data['bat_types']['link_type'] = array(
    'field' => array(
      'title' => t('Link'),
      'help' => t('Provide a link to the Type.'),
      'handler' => 'bat_type_handler_link_field',
    ),
  );
  $data['bat_types']['edit_type'] = array(
    'field' => array(
      'title' => t('Edit Link'),
      'help' => t('Provide a link to the edit form for the type.'),
      'handler' => 'bat_type_handler_edit_link_field',
    ),
  );

  $data['bat_types']['calendars'] = array(
    'field' => array(
      'title' => t('Event Management'),
      'help' => t('Display links to manage all calendars for this Type.'),
      'handler' => 'bat_type_handler_type_calendars_field',
    ),
  );

  // The content of this field is decided based on the menu structure
  // under bat/config/type/%type_id/op
  $data['bat_types']['operations'] = array(
    'field' => array(
      'title' => t('Operations links'),
      'help' => t('Display all operations available for this type.'),
      'handler' => 'bat_type_handler_type_operations_field',
    ),
  );

  $data['views_entity_bat_unit']['megarow_links']['field']['handler'] = 'bat_unit_handler_field_megarow_links';
}
