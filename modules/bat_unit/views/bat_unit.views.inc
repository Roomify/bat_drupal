<?php

/**
 * @file
 *
 * Field handlers for BAT units.
 *
 */


/**
 * Implements hook_views_data().
 */
function bat_unit_views_data_alter(&$data) {

  $data['bat_units']['type']['field'] = array(
    'title' => t('Booking Unit Type'),
    'help' => t('Booking Unit Type Label.'),
    'handler' => 'bat_unit_handler_unit_type_field',
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
  // This content of this field are decided based on the menu structure that
  // follows bat/unit/%unit_id/op
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

}
