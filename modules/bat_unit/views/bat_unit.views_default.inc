<?php

/**
 * @file
 */

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
  /* Field: Type: Event Management */
  $handler->display->display_options['fields']['calendars']['id'] = 'calendars';
  $handler->display->display_options['fields']['calendars']['table'] = 'bat_types';
  $handler->display->display_options['fields']['calendars']['field'] = 'calendars';
  $handler->display->display_options['fields']['calendars']['label'] = 'Calendars';
  $handler->display->display_options['fields']['calendars']['hide_empty'] = TRUE;
  /* Field: Type: Operations links */
  $handler->display->display_options['fields']['operations']['id'] = 'operations';
  $handler->display->display_options['fields']['operations']['table'] = 'bat_types';
  $handler->display->display_options['fields']['operations']['field'] = 'operations';
  $handler->display->display_options['fields']['operations']['label'] = 'Operations';

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
  $handler->display->display_options['pager']['options']['items_per_page'] = '50';
  $handler->display->display_options['pager']['options']['offset'] = '0';
  $handler->display->display_options['pager']['options']['id'] = '0';
  $handler->display->display_options['pager']['options']['quantity'] = '9';
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
  /* No results behavior: Global: Text area */
  $handler->display->display_options['empty']['area']['id'] = 'area';
  $handler->display->display_options['empty']['area']['table'] = 'views';
  $handler->display->display_options['empty']['area']['field'] = 'area';
  $handler->display->display_options['empty']['area']['label'] = 'Empty Text';
  $handler->display->display_options['empty']['area']['empty'] = TRUE;
  $handler->display->display_options['empty']['area']['content'] = 'There are no Units of this Type yet!';
  $handler->display->display_options['empty']['area']['format'] = 'filtered_text';
  /* Field: Bulk operations: Unit */
  $handler->display->display_options['fields']['views_bulk_operations']['id'] = 'views_bulk_operations';
  $handler->display->display_options['fields']['views_bulk_operations']['table'] = 'bat_units';
  $handler->display->display_options['fields']['views_bulk_operations']['field'] = 'views_bulk_operations';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['display_type'] = '0';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['enable_select_all_pages'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['row_clickable'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['force_single'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['entity_load_capacity'] = '10';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_operations'] = array(
    'action::views_bulk_operations_delete_item' => array(
      'selected' => 1,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::bat_unit_publish' => array(
      'selected' => 1,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::bat_event_unit_set_state' => array(
      'selected' => 1,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::bat_unit_unpublish' => array(
      'selected' => 1,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
  );
  /* Field: Unit: Unit ID */
  $handler->display->display_options['fields']['unit_id']['id'] = 'unit_id';
  $handler->display->display_options['fields']['unit_id']['table'] = 'bat_units';
  $handler->display->display_options['fields']['unit_id']['field'] = 'unit_id';
  $handler->display->display_options['fields']['unit_id']['separator'] = '';
  /* Field: Unit: Label */
  $handler->display->display_options['fields']['name']['id'] = 'name';
  $handler->display->display_options['fields']['name']['table'] = 'bat_units';
  $handler->display->display_options['fields']['name']['field'] = 'name';
  /* Field: Unit: Status */
  $handler->display->display_options['fields']['status']['id'] = 'status';
  $handler->display->display_options['fields']['status']['table'] = 'bat_units';
  $handler->display->display_options['fields']['status']['field'] = 'status';
  /* Field: Unit: Megarow links */
  $handler->display->display_options['fields']['megarow_links']['id'] = 'megarow_links';
  $handler->display->display_options['fields']['megarow_links']['table'] = 'views_entity_bat_unit';
  $handler->display->display_options['fields']['megarow_links']['field'] = 'megarow_links';
  $handler->display->display_options['fields']['megarow_links']['label'] = 'Operations';
  $handler->display->display_options['fields']['megarow_links']['alter']['alter_text'] = TRUE;
  $handler->display->display_options['fields']['megarow_links']['megarow'] = array(
    'links' => 'Edit Unit|admin/bat/config/units/unit/[unit_id]/edit',
  );
  /* Contextual filter: Unit: Type_id */
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
  $handler->display->display_options['path'] = 'admin/bat/config/types/manage/%bat_type/units';
  $handler->display->display_options['menu']['type'] = 'tab';
  $handler->display->display_options['menu']['title'] = 'Units';
  $handler->display->display_options['menu']['description'] = 'View & Edit Units of this Type';
  $handler->display->display_options['menu']['weight'] = '0';
  $handler->display->display_options['menu']['name'] = 'management';
  $handler->display->display_options['menu']['context'] = 1;
  $handler->display->display_options['menu']['context_only_inline'] = 0;
  $views['units'] = $view;

  $view = new view();
  $view->name = 'type_revisions';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'bat_types_revision';
  $view->human_name = 'Type revisions';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Type revisions';
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'perm';
  $handler->display->display_options['access']['perm'] = 'bypass bat_type entities access';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '50';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'revision_id' => 'revision_id',
    'revision_timestamp' => 'revision_timestamp',
    'name_1' => 'name_1',
    'name' => 'name',
    'log' => 'log',
    'status' => 'status',
    'operations' => 'operations',
  );
  $handler->display->display_options['style_options']['default'] = 'revision_id';
  $handler->display->display_options['style_options']['info'] = array(
    'revision_id' => array(
      'sortable' => 1,
      'default_sort_order' => 'desc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'revision_timestamp' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
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
    'name' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'log' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'status' => array(
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
  /* Relationship: Type revision: User */
  $handler->display->display_options['relationships']['revision_uid']['id'] = 'revision_uid';
  $handler->display->display_options['relationships']['revision_uid']['table'] = 'bat_types_revision';
  $handler->display->display_options['relationships']['revision_uid']['field'] = 'revision_uid';
  /* Field: Type revision: Revision ID */
  $handler->display->display_options['fields']['revision_id']['id'] = 'revision_id';
  $handler->display->display_options['fields']['revision_id']['table'] = 'bat_types_revision';
  $handler->display->display_options['fields']['revision_id']['field'] = 'revision_id';
  $handler->display->display_options['fields']['revision_id']['label'] = 'Revision';
  /* Field: Type revision: Revision date */
  $handler->display->display_options['fields']['revision_timestamp']['id'] = 'revision_timestamp';
  $handler->display->display_options['fields']['revision_timestamp']['table'] = 'bat_types_revision';
  $handler->display->display_options['fields']['revision_timestamp']['field'] = 'revision_timestamp';
  $handler->display->display_options['fields']['revision_timestamp']['label'] = 'Created on';
  $handler->display->display_options['fields']['revision_timestamp']['date_format'] = 'bat';
  $handler->display->display_options['fields']['revision_timestamp']['second_date_format'] = 'bat_daily';
  /* Field: User: Name */
  $handler->display->display_options['fields']['name_1']['id'] = 'name_1';
  $handler->display->display_options['fields']['name_1']['table'] = 'users';
  $handler->display->display_options['fields']['name_1']['field'] = 'name';
  $handler->display->display_options['fields']['name_1']['relationship'] = 'revision_uid';
  $handler->display->display_options['fields']['name_1']['label'] = 'Created by';
  /* Field: Type revision: Name */
  $handler->display->display_options['fields']['name']['id'] = 'name';
  $handler->display->display_options['fields']['name']['table'] = 'bat_types_revision';
  $handler->display->display_options['fields']['name']['field'] = 'name';
  /* Field: Type revision: Log message */
  $handler->display->display_options['fields']['log']['id'] = 'log';
  $handler->display->display_options['fields']['log']['table'] = 'bat_types_revision';
  $handler->display->display_options['fields']['log']['field'] = 'log';
  /* Field: Type revision: Revision status */
  $handler->display->display_options['fields']['status']['id'] = 'status';
  $handler->display->display_options['fields']['status']['table'] = 'bat_types_revision';
  $handler->display->display_options['fields']['status']['field'] = 'status';
  $handler->display->display_options['fields']['status']['label'] = 'Status';
  /* Field: Type revision: Operations links */
  $handler->display->display_options['fields']['operations']['id'] = 'operations';
  $handler->display->display_options['fields']['operations']['table'] = 'bat_types_revision';
  $handler->display->display_options['fields']['operations']['field'] = 'operations';
  $handler->display->display_options['fields']['operations']['label'] = '';
  $handler->display->display_options['fields']['operations']['element_label_colon'] = FALSE;
  /* Contextual filter: Type revision: Type ID */
  $handler->display->display_options['arguments']['type_id']['id'] = 'type_id';
  $handler->display->display_options['arguments']['type_id']['table'] = 'bat_types_revision';
  $handler->display->display_options['arguments']['type_id']['field'] = 'type_id';
  $handler->display->display_options['arguments']['type_id']['default_action'] = 'empty';
  $handler->display->display_options['arguments']['type_id']['default_argument_type'] = 'fixed';
  $handler->display->display_options['arguments']['type_id']['summary']['number_of_records'] = '0';
  $handler->display->display_options['arguments']['type_id']['summary']['format'] = 'default_summary';
  $handler->display->display_options['arguments']['type_id']['summary_options']['items_per_page'] = '25';

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page');
  $handler->display->display_options['path'] = 'admin/bat/config/types/manage/%/revisions';
  $handler->display->display_options['menu']['type'] = 'tab';
  $handler->display->display_options['menu']['title'] = 'Revisions';
  $handler->display->display_options['menu']['weight'] = '0';
  $handler->display->display_options['menu']['context'] = 0;
  $handler->display->display_options['menu']['context_only_inline'] = 0;
  $views['type_revisions'] = $view;

  return $views;
}
