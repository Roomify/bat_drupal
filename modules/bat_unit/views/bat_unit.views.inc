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
      'title' => t('Calendar links'),
      'help' => t('Display links to manage all calendars for this Type.'),
      'handler' => 'bat_type_handler_type_calendars_field',
    ),
  );

}

/**
 * Implements hook_views_default_views().
 */
function bat_unit_views_default_views() {
  $views = array();

  $view = new view();
  $view->name = 'unit_management';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'bat_types';
  $view->human_name = 'Unit Management';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Unit Management';
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'perm';
  $handler->display->display_options['access']['perm'] = 'update any bat_type entity';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '25';
  $handler->display->display_options['style_plugin'] = 'table';
  /* Field: Type: Type ID */
  $handler->display->display_options['fields']['type_id']['id'] = 'type_id';
  $handler->display->display_options['fields']['type_id']['table'] = 'bat_types';
  $handler->display->display_options['fields']['type_id']['field'] = 'type_id';
  /* Field: Type: Type */
  $handler->display->display_options['fields']['type']['id'] = 'type';
  $handler->display->display_options['fields']['type']['table'] = 'bat_types';
  $handler->display->display_options['fields']['type']['field'] = 'type';
  $handler->display->display_options['fields']['type']['label'] = 'Bundle';
  /* Field: Type: Name */
  $handler->display->display_options['fields']['name']['id'] = 'name';
  $handler->display->display_options['fields']['name']['table'] = 'bat_types';
  $handler->display->display_options['fields']['name']['field'] = 'name';
  /* Field: Type: Status */
  $handler->display->display_options['fields']['status']['id'] = 'status';
  $handler->display->display_options['fields']['status']['table'] = 'bat_types';
  $handler->display->display_options['fields']['status']['field'] = 'status';
  $handler->display->display_options['fields']['status']['label'] = 'Published';
  $handler->display->display_options['fields']['status']['separator'] = '';
  /* Field: Type: Calendar links */
  $handler->display->display_options['fields']['calendars']['id'] = 'calendars';
  $handler->display->display_options['fields']['calendars']['table'] = 'bat_types';
  $handler->display->display_options['fields']['calendars']['field'] = 'calendars';
  $handler->display->display_options['fields']['calendars']['label'] = 'Calendars';
  /* Field: Type: Edit Link */
  $handler->display->display_options['fields']['edit_type']['id'] = 'edit_type';
  $handler->display->display_options['fields']['edit_type']['table'] = 'bat_types';
  $handler->display->display_options['fields']['edit_type']['field'] = 'edit_type';
  $handler->display->display_options['fields']['edit_type']['label'] = 'Operations';

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page');
  $handler->display->display_options['path'] = 'admin/bat/unit-management';
  $handler->display->display_options['menu']['type'] = 'normal';
  $handler->display->display_options['menu']['title'] = 'Unit Management';
  $handler->display->display_options['menu']['description'] = 'Manage Bat Types and their Units';
  $handler->display->display_options['menu']['weight'] = '0';
  $handler->display->display_options['menu']['name'] = 'management';
  $handler->display->display_options['menu']['context'] = 0;
  $handler->display->display_options['menu']['context_only_inline'] = 0;

  $views['unit_management'] = $view;

  $view = new view();
  $view->name = 'units';
  $view->description = 'View Bat Units associated with a Bat Type';
  $view->tag = 'default';
  $view->base_table = 'bat_units';
  $view->human_name = 'Units';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Units';
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '25';
  $handler->display->display_options['style_plugin'] = 'table_megarows';
  $handler->display->display_options['style_options']['columns'] = array(
    'unit_id' => 'unit_id',
    'name' => 'name',
    'megarow_links' => 'megarow_links',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'unit_id' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'name' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'megarow_links' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
  );
  $handler->display->display_options['style_options']['autoclose'] = TRUE;
  $handler->display->display_options['style_options']['scroll_padding'] = '120';
  $handler->display->display_options['row_plugin'] = 'fields';
  /* Field: Units: Units ID */
  $handler->display->display_options['fields']['unit_id']['id'] = 'unit_id';
  $handler->display->display_options['fields']['unit_id']['table'] = 'bat_units';
  $handler->display->display_options['fields']['unit_id']['field'] = 'unit_id';
  $handler->display->display_options['fields']['unit_id']['label'] = 'Unit ID';
  $handler->display->display_options['fields']['unit_id']['separator'] = '';
  /* Field: Units: Name */
  $handler->display->display_options['fields']['name']['id'] = 'name';
  $handler->display->display_options['fields']['name']['table'] = 'bat_units';
  $handler->display->display_options['fields']['name']['field'] = 'name';
  /* Field: Units: Megarow links */
  $handler->display->display_options['fields']['megarow_links']['id'] = 'megarow_links';
  $handler->display->display_options['fields']['megarow_links']['table'] = 'views_entity_bat_unit';
  $handler->display->display_options['fields']['megarow_links']['field'] = 'megarow_links';
  $handler->display->display_options['fields']['megarow_links']['alter']['alter_text'] = TRUE;
  $handler->display->display_options['fields']['megarow_links']['megarow'] = array(
    'links' => 'Edit Unit|admin/bat/config/units/unit/[unit_id]/edit',
  );
  /* Contextual filter: Units: Type_id */
  $handler->display->display_options['arguments']['type_id']['id'] = 'type_id';
  $handler->display->display_options['arguments']['type_id']['table'] = 'bat_units';
  $handler->display->display_options['arguments']['type_id']['field'] = 'type_id';
  $handler->display->display_options['arguments']['type_id']['default_action'] = 'default';
  $handler->display->display_options['arguments']['type_id']['default_argument_type'] = 'raw';
  $handler->display->display_options['arguments']['type_id']['default_argument_options']['index'] = '5';
  $handler->display->display_options['arguments']['type_id']['summary']['number_of_records'] = '0';
  $handler->display->display_options['arguments']['type_id']['summary']['format'] = 'default_summary';
  $handler->display->display_options['arguments']['type_id']['summary_options']['items_per_page'] = '25';

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page');
  $handler->display->display_options['path'] = 'admin/bat/config/types/type/%/units';
  $handler->display->display_options['menu']['type'] = 'tab';
  $handler->display->display_options['menu']['title'] = 'Units';
  $handler->display->display_options['menu']['weight'] = '0';
  $handler->display->display_options['menu']['name'] = 'management';
  $handler->display->display_options['menu']['context'] = 0;
  $handler->display->display_options['menu']['context_only_inline'] = 0;
  $views['units'] = $view;

  return $views;
}
