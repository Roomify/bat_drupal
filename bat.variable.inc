<?php
/**
 * @file
 * Variable API module. Definition for some xample variables
 */

/**
 * Implements hook_variable_info().
 */
function rooms_variable_info($options) {
  $variables['rooms_arrival_date'] = array(
    'type' => 'string',
    'title' => t('Arrival Date'),
    'default' => 'Arrival Date',
    'description' => t('Change text : Arrival Date'),
    'localize' => TRUE,
    'group' => 'rooms',
  );
  
  $variables['rooms_departure_date'] = array(
    'type' => 'string',
    'title' => t('Departure Date'),
    'default' => 'Departure Date',
    'description' => t('Change text : Departure Date'),
    'localize' => TRUE,
    'group' => 'rooms',
  );

  return $variables;
}

/**
 * Implements hook_variable_group_info().
 */
function rooms_variable_group_info() {
  $groups['rooms'] = array(
    'title' => t('Rooms'),
    'description' => t('Variable examples of different types.'),
    'access' => 'administer site configuration',
    'path' => array('admin/rooms/'),
  );
  return $groups;
}
