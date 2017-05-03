<?php

/**
 * @file
 * BatType editing UI.
 *
 * We make very little use of the EntityAPI interface for this - preferring
 * instead to use views. That offers more flexibility to change a UI that will,
 * more often than not, be end-user facing.
 */

/**
 * UI controller.
 */
class BatTypeUIController extends EntityDefaultUIController {

  /**
   * Overrides hook_menu() defaults.
   */
  public function hook_menu() {
    $items = parent::hook_menu();

    $id_count = count(explode('/', $this->path));
    $wildcard = isset($this->entityInfo['admin ui']['menu wildcard']) ? $this->entityInfo['admin ui']['menu wildcard'] : '%' . $this->entityType;

    $items[$this->path]['description'] = 'Add, edit, and update Types.';
    $items[$this->path]['weight'] = 10;

    // Change the add page menu to multiple types of entities.
    $items[$this->path . '/add']['title'] = 'Add a Type';
    $items[$this->path . '/add']['description'] = 'Create a new Type.';
    $items[$this->path . '/add']['page callback'] = 'bat_type_add_page';
    $items[$this->path . '/add']['access callback'] = 'bat_type_add_access';
    unset($items[$this->path . '/add']['title callback']);

    // Add menu items to add each different bundle of Bat Types.
    foreach (bat_unit_get_type_bundles() as $bundle) {
      $items[$this->path . '/add/' . $bundle->type] = array(
        'title' => 'Add @type_bundle_label type',
        'title arguments' => array('@type_bundle_label' => $bundle->label),
        'page callback' => 'bat_type_create_form_wrapper',
        'page arguments' => array($bundle->type),
        'access callback' => 'bat_type_access',
        'access arguments' => array('create', bat_type_create(array('type' => $bundle->type, 'uid' => 0))),
        'file' => 'bat_type.admin.inc',
        'file path' => drupal_get_path('module', $this->entityInfo['module']),
      );
    }

    // Loading and editing type entities.
    $items[$this->path . '/manage/' . $wildcard] = array(
      'title callback' => 'bat_type_page_title',
      'title arguments' => array($id_count + 1),
      'page callback' => 'bat_type_page_view',
      'page arguments' => array($id_count + 1),
      'access callback' => 'bat_type_access',
      'access arguments' => array('view', $id_count + 1),
      'context' => MENU_CONTEXT_PAGE | MENU_CONTEXT_INLINE,
    );

    $items[$this->path . '/manage/' . $wildcard . '/view'] = array(
      'title callback' => 'bat_type_title_callback',
      'title arguments' => array($id_count + 1, 'view'),
      'type' => MENU_DEFAULT_LOCAL_TASK,
      'weight' => -10,
      'context' => MENU_CONTEXT_PAGE | MENU_CONTEXT_INLINE,
    );

    if (module_exists('diff')) {
      $items[$this->path . '/manage/' . $wildcard . '/revisions/view'] = array(
        'title' => '',
        'page callback' => 'bat_type_compare_revisions',
        'page arguments' => array($id_count + 1, $id_count + 4, $id_count + 5),
        'access callback' => TRUE,
        'type' => MENU_LOCAL_TASK,
        'file' => 'bat_type.diff.inc',
        'file path' => drupal_get_path('module', $this->entityInfo['module']),
      );
    }

    $items[$this->path . '/manage/' . $wildcard . '/revision/%/view'] = array(
      'title' => 'View Revision',
      'page callback' => 'bat_type_revision_view',
      'page arguments' => array($id_count + 1, $id_count + 3),
      'access callback' => 'bat_type_access_revision',
      'access arguments' => array('view', $id_count + 1, $id_count + 3),
      'file' => 'bat_type.admin.inc',
      'file path' => drupal_get_path('module', $this->entityInfo['module']),
    );

    $items[$this->path . '/manage/' . $wildcard . '/revision/%/revert'] = array(
      'page callback' => 'drupal_get_form',
      'page arguments' => array('bat_type_revision_revert_confirm', $id_count + 1, $id_count + 3),
      'access callback' => 'bat_type_access_revision',
      'access arguments' => array('revert', $id_count + 1, $id_count + 3),
      'file' => 'bat_type.admin.inc',
      'file path' => drupal_get_path('module', $this->entityInfo['module']),
    );

    $items[$this->path . '/manage/' . $wildcard . '/revision/%/delete'] = array(
      'page callback' => 'drupal_get_form',
      'page arguments' => array('bat_type_revision_delete_confirm', $id_count + 1, $id_count + 3),
      'access callback' => 'bat_type_access_revision',
      'access arguments' => array('delete', $id_count + 1, $id_count + 3),
      'file' => 'bat_type.admin.inc',
      'file path' => drupal_get_path('module', $this->entityInfo['module']),
    );

    $items[$this->path . '/manage/' . $wildcard . '/edit'] = array(
      'title callback' => 'bat_type_title_callback',
      'title arguments' => array($id_count + 1, 'edit'),
      'page callback' => 'bat_type_form_wrapper',
      'page arguments' => array($id_count + 1),
      'access callback' => 'bat_type_access',
      'access arguments' => array('update', $id_count + 1),
      'weight' => 0,
      'type' => MENU_LOCAL_TASK,
      'context' => MENU_CONTEXT_PAGE | MENU_CONTEXT_INLINE,
      'file' => 'bat_type.admin.inc',
      'file path' => drupal_get_path('module', $this->entityInfo['module']),
    );

    $items[$this->path . '/manage/' . $wildcard . '/delete'] = array(
      'title' => 'Delete',
      'page callback' => 'bat_type_delete_form_wrapper',
      'page arguments' => array($id_count + 1),
      'access callback' => 'bat_type_access',
      'access arguments' => array('delete', $id_count + 1),
      'type' => MENU_LOCAL_TASK,
      'context' => MENU_CONTEXT_INLINE,
      'weight' => 10,
      'file' => 'bat_type.admin.inc',
      'file path' => drupal_get_path('module', $this->entityInfo['module']),
    );

    // Menu item for viewing types.
    $items['type/' . $wildcard] = array(
      'title callback' => 'bat_type_page_title',
      'title arguments' => array(1),
      'page callback' => 'bat_type_page_view',
      'page arguments' => array(1),
      'access callback' => 'bat_type_access',
      'access arguments' => array('view', 1),
      'type' => MENU_CALLBACK,
    );

    $items[$this->path . '/manage/' . $wildcard . '/units/add'] = array(
      'title' => 'Add Units',
      'page callback' => 'bat_type_add_units_page',
      'page arguments' => array(5),
      'access arguments' => array('create bat_unit entities'),
      'file' => 'bat_type.admin.inc',
      'file path' => drupal_get_path('module', $this->entityInfo['module']),
      'type' => MENU_CALLBACK,
    );
    foreach (bat_unit_get_bundles() as $bundle) {
      $items[$this->path . '/manage/' . $wildcard . '/units/add/' . $bundle->type] = array(
        'title' => 'Add @unit_bundle_label unit',
        'title arguments' => array('@unit_bundle_label' => $bundle->label),
        'page callback' => 'bat_type_add_units_form_wrapper',
        'page arguments' => array(5, 8),
        'access callback' => 'bat_unit_access',
        'access arguments' => array('create', bat_unit_create(array('type' => $bundle->type, 'uid' => 0))),
        'file' => 'bat_type.admin.inc',
        'file path' => drupal_get_path('module', $this->entityInfo['module']),
      );
    }

    return $items;
  }

  /**
   * Overrides overviewTableHeaders() defaults.
   */
  protected function overviewTableHeaders($conditions, $rows, $additional_header = array()) {
    $header = array(
      t('ID'),
      t('Name'),
      t('Bundle'),
    );
    if (!empty($this->entityInfo['exportable'])) {
      $header[] = t('Status');
    }
    // Add operations with the right colspan.
    $header[] = array('data' => t('Operations'), 'colspan' => $this->operationCount());
    return $header;
  }

  /**
   * Overrides overviewTableRow() defaults.
   */
  protected function overviewTableRow($conditions, $id, $entity, $additional_cols = array()) {
    $entity_uri = entity_uri($this->entityType, $entity);

    $row[] = $id;

    $row[] = array(
      'data' => array(
        '#theme' => 'entity_ui_overview_item',
        '#label' => entity_label($this->entityType, $entity),
        '#name' => !empty($this->entityInfo['exportable']) ? entity_id($this->entityType, $entity) : FALSE,
        '#url' => $entity_uri ? $entity_uri : FALSE,
        '#entity_type' => $this->entityType,
      ),
    );

    $type_bundle = bat_type_bundle_load($entity->type);
    $row[] = $type_bundle->label;

    // Add a row for the exportable status.
    if (!empty($this->entityInfo['exportable'])) {
      $row[] = array(
        'data' => array(
          '#theme' => 'entity_status',
          '#status' => $entity->{$this->statusKey},
        ),
      );
    }
    // In case this is a bundle, we add links to the field ui tabs.
    $field_ui = !empty($this->entityInfo['bundle of']) && entity_type_is_fieldable($this->entityInfo['bundle of']) && module_exists('field_ui');
    // For exportable entities we add an export link.
    $exportable = !empty($this->entityInfo['exportable']);
    // If i18n integration is enabled, add a link to the translate tab.
    $i18n = !empty($this->entityInfo['i18n controller class']);

    // Add operations depending on the status.
    if (entity_has_status($this->entityType, $entity, ENTITY_FIXED)) {
      $row[] = array('data' => l(t('clone'), $this->path . '/manage/' . $id . '/clone'), 'colspan' => $this->operationCount());
    }
    else {
      $row[] = l(t('edit'), $this->path . '/manage/' . $id . '/edit');

      if ($field_ui) {
        $row[] = l(t('manage fields'), $this->path . '/manage/' . $id . '/fields');
        $row[] = l(t('manage display'), $this->path . '/manage/' . $id . '/display');
      }
      if ($i18n) {
        $row[] = l(t('translate'), $this->path . '/manage/' . $id . '/translate');
      }
      if ($exportable) {
        $row[] = l(t('clone'), $this->path . '/manage/' . $id . '/clone');
      }

      if (empty($this->entityInfo['exportable']) || !entity_has_status($this->entityType, $entity, ENTITY_IN_CODE)) {
        $row[] = l(t('delete'), $this->path . '/manage/' . $id . '/delete', array('query' => drupal_get_destination()));
      }
      elseif (entity_has_status($this->entityType, $entity, ENTITY_OVERRIDDEN)) {
        $row[] = l(t('revert'), $this->path . '/manage/' . $id . '/revert', array('query' => drupal_get_destination()));
      }
      else {
        $row[] = '';
      }
    }
    if ($exportable) {
      $row[] = l(t('export'), $this->path . '/manage/' . $id . '/export');
    }
    return $row;
  }

  /**
   * Creates the markup for the add Bat Type Entities page within the class
   * so it can easily be extended/overridden.
   */
  public function addPage() {
    $item = menu_get_item();
    $bundles = bat_unit_get_type_bundles();

    // If there is only one unit bundle go straight to that page.
    if (count($bundles) == 1) {
      $bundle = reset($bundles);
      drupal_goto($this->path . '/add/' . $bundle->type);
    }

    $items = array();
    foreach ($bundles as $bundle) {
      $items[] = array(
        'title' => t('Add @type_bundle_label type', array('@type_bundle_label' => $bundle->label)),
        'href' => $this->path . '/add/' . $bundle->type,
        'description' => '',
      );
    }

    return array(
      '#theme' => 'bat_type_add_list',
      '#content' => $items,
    );
  }

  /**
   * Creates the markup for the add Bat Units pages when units are added through
   * the Type configuration page.
   */
  public function addUnitsPage() {
    $item = menu_get_item();
    $bundles = bat_unit_get_bundles();

    // If there is only one unit bundle go straight to that page.
    if (count($bundles) == 1) {
      drupal_goto($item['href'] . '/' . array_shift($bundles)->type);
    }

    $content['bundles'] = $bundles;
    $content['item'] = $item;
    return array(
      '#theme' => 'bat_type_unit_add_list',
      '#content' => $content,
    );
  }

}

/**
 * Form callback: edit a type.
 */
function bat_type_form($form, &$form_state, $type, $op = 'edit') {
  // Add the breadcrumb for the form's location.
  bat_type_set_breadcrumb();
  drupal_set_title(t('Edit @type_name', array('@type_name' => $type->name)));

  $type->date = format_date($type->created, 'custom', 'Y-m-d H:i:s O');
  $account = user_load($type->uid);
  $type->author_name = isset($account->name) ? $account->name : '';

  return bat_type_edit_form($form, $form_state, $type);
}

/**
 * Form callback wrapper: edit a type.
 *
 * @param $type
 *   The Battype object being edited by this form.
 *
 * @see bat_type_edit_form()
 */
function bat_type_form_wrapper($type) {
  // Add the breadcrumb for the form's location.
  bat_type_set_breadcrumb();
  drupal_set_title(t('Edit @type_name', array('@type_name' => $type->name)));

  $type->date = format_date($type->created, 'custom', 'Y-m-d H:i:s O');
  $account = user_load($type->uid);
  $type->author_name = isset($account->name) ? $account->name : '';

  return drupal_get_form('bat_type_edit_form', $type);
}

/**
 * Form callback wrapper: create a type.
 *
 * @param $type
 *   The type type for the type to be created.
 */
function bat_type_create_form_wrapper($type) {
  global $user;

  // Add the breadcrumb for the form's location.
  bat_type_set_breadcrumb();

  $type = bat_type_create(array('type' => $type, 'uid' => $user->uid));
  $type->created = REQUEST_TIME;
  $type->author_name = $user->name;
  $type->status = 1;

  return drupal_get_form('bat_type_edit_form', $type);
}

/**
 * Form callback wrapper: create units associated with a type.
 *
 * @param $type
 *   The type of units to create
 */
function bat_type_add_units_form_wrapper($type, $unit_type) {
  global $user;

  // Add the breadcrumb for the form's location.
  bat_type_set_breadcrumb();

  $unit_model = bat_unit_create(array('type' => $unit_type, 'uid' => $user->uid));
  $unit_model->type_id = $type->type_id;
  $unit_model->created = REQUEST_TIME;
  $unit_model->author_name = $user->name;
  $unit_model->status = 1;

  return drupal_get_form('bat_type_add_units_form', $unit_model);
}

/**
 * Form callback wrapper: delete a type.
 *
 * @param BatType $type
 *   The type object being edited by this form.
 *
 * @see bat_type_edit_form()
 */
function bat_type_delete_form_wrapper(BatType $type) {
  // Add the breadcrumb for the form's location.
  bat_type_set_breadcrumb();
  return drupal_get_form('bat_type_delete_form', $type);
}

/**
 * Form callback: create or edit a type.
 *
 * @param BatType $type
 *   The BatType object to edit or, for a creation form, an empty type object
 *   with only a type bundle defined.
 */
function bat_type_edit_form($form, &$form_state, BatType $type) {
  $form['#attributes']['class'][] = 'bat-management-form bat-type-edit-form';

  $form['type'] = array(
    '#type' => 'value',
    '#value' => $type->type,
  );

  // Add the default field elements.
  $form['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Type name'),
    '#default_value' => isset($type->name) ? $type->name : '',
    '#maxlength' => 255,
    '#required' => TRUE,
    '#weight' => -99,
  );

  // Add the field related form elements.
  $form_state['bat_type'] = $type;
  field_attach_form('bat_type', $type, $form, $form_state, entity_language('bat_type', $type));
  $form['additional_settings'] = array(
    '#type' => 'vertical_tabs',
    '#weight' => 99,
  );

  // Type author information for administrators.
  $form['author'] = array(
    '#type' => 'fieldset',
    '#access' => user_access('bypass bat_type entities access'),
    '#title' => t('Authoring information'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#group' => 'additional_settings',
    '#attributes' => array(
      'class' => array('type-form-author'),
    ),
    '#attached' => array(
      'js' => array(
        array(
          'type' => 'setting',
          'data' => array('anonymous' => variable_get('anonymous', t('Anonymous'))),
        ),
      ),
    ),
    '#weight' => 90,
  );
  $form['author']['author_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Authored by'),
    '#maxlength' => 60,
    '#autocomplete_path' => 'user/autocomplete',
    '#default_value' => !empty($type->author_name) ? $type->author_name : '',
    '#weight' => -1,
    '#description' => t('Leave blank for %anonymous.', array('%anonymous' => variable_get('anonymous', t('Anonymous')))),
  );
  $form['author']['date'] = array(
    '#type' => 'textfield',
    '#title' => t('Authored on'),
    '#maxlength' => 25,
    '#description' => t('Format: %time. The date format is YYYY-MM-DD and %timezone is the time zone offset from UTC. Leave blank to use the time of form submission.', array('%time' => !empty($type->date) ? date_format(date_create($type->date), 'Y-m-d H:i:s O') : format_date($type->created, 'custom', 'Y-m-d H:i:s O'), '%timezone' => !empty($type->date) ? date_format(date_create($type->date), 'O') : format_date($type->created, 'custom', 'O'))),
    '#default_value' => !empty($type->date) ? $type->date : '',
  );

  $form['revisions'] = array(
    '#type' => 'fieldset',
    '#access' => user_access('bypass bat_type entities access'),
    '#title' => t('Revision information'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#group' => 'additional_settings',
    '#attributes' => array(
      'class' => array('type-form-revisions'),
    ),
    '#weight' => 95,
  );

  if (module_exists('revisioning')) {
    $form['revisions']['log'] = array(
      '#type' => 'textarea',
      '#title' => !empty($type->type_id) ? t('Update log message') : t('Creation log message'),
      '#rows' => 4,
      '#description' => t('Provide an explanation of the changes you are making. This will provide a meaningful history of changes to this type.'),
    );

    $options = array();
    if (!empty($type->type_id)) {
      $options[REVISIONING_NO_REVISION] = t('Modify current revision, no moderation');
    }
    $options[REVISIONING_NEW_REVISION_NO_MODERATION] = t('Create new revision, no moderation');
    $options[REVISIONING_NEW_REVISION_WITH_MODERATION] = t('Create new revision and moderate');

    $form['revisions']['revision_operation'] = array(
      '#title' => t('Revision creation and moderation options'),
      '#description' => t('Moderation means that the new revision is not publicly visible until approved by someone with the appropriate permissions.'),
      '#type' => 'radios',
      '#options' => $options,
      '#default_value' => isset($type->type_id) ? REVISIONING_NEW_REVISION_WITH_MODERATION : REVISIONING_NEW_REVISION_NO_MODERATION,
    );

    if (variable_get('revisioning_no_moderation_by_default', FALSE)) {
      $form['revisions']['revision_operation']['#default_value'] = REVISIONING_NEW_REVISION_NO_MODERATION;
    }

    if (!empty($type->type_id)) {
      $revision_count = bat_type_get_number_of_revisions_newer_than($type->revision_id, $type->type_id);

      if ($revision_count == 1) {
        drupal_set_message(t('Please note there is one revision more recent than the one you are about to edit.'), 'warning');
      }
      elseif ($revision_count > 1) {
        drupal_set_message(t('Please note there are @count revisions more recent than the one you are about to edit.',
          array('@count' => $revision_count)), 'warning');
      }
    }
  }
  else {
    if (!empty($type->type_id)) {
      $type_bundle = bat_type_bundle_load($type->type);

      $form['revisions']['revision'] = array(
        '#type' => 'checkbox',
        '#title' => t('Create new revision on update'),
        '#description' => t('If an update log message is entered, a revision will be created even if this is unchecked.'),
        '#default_value' => (isset($type_bundle->data['revision'])) ? $type_bundle->data['revision'] : 0,
      );
    }
    $form['revisions']['log'] = array(
      '#type' => 'textarea',
      '#title' => !empty($type->type_id) ? t('Update log message') : t('Creation log message'),
      '#rows' => 4,
      '#description' => t('Provide an explanation of the changes you are making. This will provide a meaningful history of changes to this type.'),
    );
  }

  // Type publishing options for administrators.
  $form['options'] = array(
    '#type' => 'fieldset',
    '#access' => user_access('bypass bat_type entities access'),
    '#title' => t('Publishing options'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#group' => 'additional_settings',
    '#attributes' => array(
      'class' => array('type-form-published'),
    ),
    '#weight' => 95,
  );
  $form['options']['status'] = array(
    '#type' => 'checkbox',
    '#title' => t('Published'),
    '#default_value' => $type->status,
  );

  $form['actions'] = array(
    '#type' => 'actions',
    '#tree' => FALSE,
  );
  // We add the form's #submit array to this button along with the actual submit
  // handler to preserve any submit handlers added by a form callback_wrapper.
  $submit = array();
  if (!empty($form['#submit'])) {
    $submit += $form['#submit'];
  }
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Type'),
    '#submit' => $submit + array('bat_type_edit_form_submit'),
  );
  if (!empty($type->name) && bat_type_access('delete', $type)) {
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete Type'),
      '#suffix' => l(t('Cancel'), 'admin/bat/config/types'),
      '#submit' => $submit + array('bat_type_form_submit_delete'),
      '#weight' => 45,
    );
  }
  // We append the validate handler to #validate in case a form callback_wrapper
  // is used to add validate handlers earlier.
  $form['#validate'][] = 'bat_type_edit_form_validate';

  return $form;
}

/**
 * Form API validate callback for the booking type form.
 */
function bat_type_edit_form_validate(&$form, &$form_state) {
  // Notify field widgets to validate their data.
  entity_form_field_validate('bat_type', $form, $form_state);
}

/**
 * Form API submit callback for the type form.
 */
function bat_type_edit_form_submit(&$form, &$form_state) {
  $type = entity_ui_controller('bat_type')->entityFormSubmitBuildEntity($form, $form_state);
  $type->created = !empty($type->date) ? strtotime($type->date) : REQUEST_TIME;

  if ($type->type_id !== '') {
    $type->changed = time();
  }

  if (module_exists('revisioning')) {
    if (isset($type->revision_operation)) {
      $type->revision = ($type->revision_operation > REVISIONING_NO_REVISION);
      if ($type->revision_operation == REVISIONING_NEW_REVISION_WITH_MODERATION) {
        $type->default_revision = FALSE;
      }
    }
  }
  else {
    // Trigger a new revision if the checkbox was enabled or a log message supplied.
    if (!empty($form_state['values']['revision']) ||
        !empty($form['change_history']['revision']['#default_value']) ||
        !empty($form_state['values']['log'])) {
      $type->revision = TRUE;
      $type->log = $form_state['values']['log'];
    }
  }

  if (isset($type->author_name)) {
    if ($account = user_load_by_name($type->author_name)) {
      $type->uid = $account->uid;
    }
    else {
      $type->uid = 0;
    }
  }

  $type->save();
  drupal_set_message(t('Type @name saved', array('@name' => $type->name)));

  $form_state['redirect'] = 'admin/bat/config/types';
}

/**
 * Form API submit callback for the delete button.
 */
function bat_type_form_submit_delete(&$form, &$form_state) {
  $destination = array();
  if (isset($_GET['destination'])) {
    $destination = drupal_get_destination();
    unset($_GET['destination']);
  }

  $form_state['redirect'] = array('admin/bat/config/types/manage/' . $form_state['bat_type']->type_id . '/delete', array('query' => $destination));
}

/**
 * Form callback: confirmation form for deleting a Type.
 *
 * @param $type
 *   The Type entity to delete.
 *
 * @see confirm_form()
 */
function bat_type_delete_form($form, &$form_state, $type) {
  $form_state['bat_type'] = $type;
  $form['#submit'][] = 'bat_type_delete_form_submit';
  $form = confirm_form($form,
    t('Are you sure you want to delete Type %name?', array('%name' => $type->name)),
    'admin/bat/config/types/manage',
    '<p>' . t('Deleting a type is going to delete the associated units and leave any events referencing those units without a unit reference. Please proceed only if you are sure this is the right thing to do.') . '</p>',
    t('Delete'),
    t('Cancel'),
    'confirm'
  );
  return $form;
}

/**
 * Submit callback for type_delete_form.
 */
function bat_type_delete_form_submit($form, &$form_state) {
  $type = $form_state['bat_type'];
  bat_type_delete($type);
  drupal_set_message(t('The Type %name has been deleted.', array('%name' => $type->name)));
  watchdog('bat', 'Deleted Type %name.', array('%name' => $type->name));
  $form_state['redirect'] = 'admin/bat/config/types';
}

/**
 * Page to add Types.
 */
function bat_type_add_page() {
  $controller = entity_ui_controller('bat_type');
  return $controller->addPage();
}

/**
 * Page to add Units to a Types.
 */
function bat_type_add_units_page() {
  $controller = entity_ui_controller('bat_type');
  return $controller->addUnitsPage();
}

/**
 * Displays the list of available Type bundles for Type creation.
 *
 * @ingroup themeable
 */
function theme_bat_type_add_list($variables) {
  $content = $variables['content'];

  if ($content) {
    $output = '<dl class="type-type-list">';
    foreach ($content as $item) {
      $output .= '<dt>' . l($item['title'], $item['href']) . '</dt>';
      $output .= '<dd>' . filter_xss_admin($item['description']) . '</dd>';
    }
    $output .= '</dl>';
  }
  else {
    if (user_access('administer type bundles')) {
      $output = '<p>' . t('Type cannot be added because you have not created any Type bundles yet. Go to the <a href="@create-type_bundle">type bundle creation page</a> to add a new type bundle.', array('@create-type_bundle' => url('admin/bat/config/type-bundles/add'))) . '</p>';
    }
    else {
      $output = '<p>' . t('No Type bundles have been created yet.') . '</p>';
    }
  }

  return $output;
}

/**
 * Displays the list of available Unit bundles for Unit creation.
 *
 * @ingroup themeable
 */
function theme_bat_type_unit_add_list($variables) {
  $bundles = $variables['content']['bundles'];
  $item = $variables['content']['item'];

  if ($bundles) {
    $output = '<dl class="unit-type-list">';
    foreach ($bundles as $unit_bundle) {
      $output .= '<dt>' . l(t('Add @unit_bundle', array('@unit_bundle' => $unit_bundle->label)), $item['href'] . '/' . $unit_bundle->type) . '</dt>';
      $output .= '<dd>' . filter_xss_admin($item['description']) . '</dd>';
    }
    $output .= '</dl>';
  }
  else {
    if (user_access('administer type bundles')) {
      $output = '<p>' . t('Unit cannot be added because you have not created any Unit bundles yet. Go to the <a href="@create-unit_bundle">unit bundle creation page</a> to add a new unit bundle.', array('@create-unit_bundle' => url('admin/bat/config/unit-bundles/add'))) . '</p>';
    }
    else {
      $output = '<p>' . t('No Unit bundles have been created yet.') . '</p>';
    }
  }

  return $output;
}

/**
 * Sets the breadcrumb for administrative BAT pages.
 */
function bat_type_set_breadcrumb() {
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    (drupal_valid_path('admin')) ? l(t('Administration'), 'admin') : '',
    (drupal_valid_path('admin/bat')) ? l(t('BAT'), 'admin/bat') : '',
    (drupal_valid_path('admin/bat/config')) ? l(t('Configuration'), 'admin/bat/config') : '',
    (drupal_valid_path('admin/bat/config/type-types')) ? l(t('Types'), 'admin/bat/config/type-types') : '',
  );

  drupal_set_breadcrumb(array_filter($breadcrumb));
}

/**
 * Unit add form.
 */
function bat_type_add_units_form($form, &$form_state, $unit_model) {
  $form['units'] = array(
    '#type' => 'textfield',
    '#title' => t('Units'),
    '#size' => 3,
    '#description' => t('Number of Units to create.'),
  );

  $form['type_id'] = array(
    '#type' => 'hidden',
    '#value' => $unit_model->type_id,
  );

  $form['unit_type'] = array(
    '#type' => 'hidden',
    '#value' => $unit_model->type,
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Add'),
  );

  $form['#attached']['css'][] = array(
    'data' => '.form-item-units label { display: inline-block; margin-right: 5px; }',
    'type' => 'inline',
  );

  return $form;
}

/**
 * Validation handler for unit add form.
 */
function bat_type_add_units_form_validate($form, &$form_state) {
  if (empty($form_state['values']['units'])) {
    form_set_error('units', t('Please enter the number of units to add.'));
  }
  elseif (!is_numeric($form_state['values']['units'])) {
    form_set_error('units', t('Please enter a valid number of units to add.'));
  }
}

/**
 * Submit handler for unit add form.
 */
function bat_type_add_units_form_submit($form, &$form_state) {
  global $user;

  // The Type this unit is associated too.
  $type_id = $form_state['values']['type_id'];

  // The unit bundle this unit belongs to.
  $unit_type = $form_state['values']['unit_type'];

  // Sanity check.
  if (!$type = bat_type_load($type_id)) {
    drupal_set_message(t('Could not load Bat Unit!'), 'warning');
    return;
  }

  $total_units = count(bat_unit_load_multiple(FALSE, array('type_id' => $type_id)));

  // Create units.
  for ($i = ($total_units + 1); $i <= ($form_state['values']['units'] + $total_units); $i++) {
    $unit = bat_unit_create(array('type' => $unit_type));
    $unit->name = $type->name . ' ' . $i;
    $unit->created = !empty($unit->date) ? strtotime($unit->date) : REQUEST_TIME;

    $unit->type_id = $type_id;
    $unit->default_state = 1;
    $unit->uid = $user->uid;
    $unit->save();
  }

  $form_state['redirect'] = 'admin/bat/config/types/manage/' . $type_id . '/units';
}

/**
 * @param BatType $type
 * @param int $revision
 */
function bat_type_revision_view(BatType $type, $revision) {
  drupal_set_title(t('Revision of %title from %date', array('%title' => $type->name, '%date' => format_date($type->revision_timestamp))), PASS_THROUGH);

  return entity_view('bat_type', array(bat_type_load_revision($revision)));
}

/**
 * @param BatType $type
 * @param int $revision
 */
function bat_type_revision_revert_confirm($form, $form_state, BatType $type, $revision) {
  $type_revision = bat_type_load_revision($revision);
  $form['#type_revision'] = $type_revision;

  return confirm_form($form, t('Are you sure you want to revert to the revision from %revision-date?', array('%revision-date' => format_date($type_revision->revision_timestamp))), 'admin/bat/config/types/manage/' . $type_revision->type_id . '/revisions', '', t('Revert'), t('Cancel'));
}

/**
 * Form submission handler for bat_type_revision_revert_confirm().
 */
function bat_type_revision_revert_confirm_submit($form, &$form_state) {
  $type_revision = $form['#type_revision'];

  $type_revision->revision = TRUE;
  $type_revision->default_revision = TRUE;

  bat_type_revisioning_event('pre revert', $type_revision);

  $type_revision->save();

  bat_type_revisioning_event('post revert', $type_revision);

  $form_state['redirect'] = 'admin/bat/config/types/manage/' . $type_revision->type_id . '/revisions';
}

/**
 * @param BatType $type
 * @param int $revision
 */
function bat_type_revision_delete_confirm($form, $form_state, BatType $type, $revision) {
  $type_revision = bat_type_load_revision($revision);
  $form['#type_revision'] = $type_revision;

  return confirm_form($form, t('Are you sure you want to delete the revision from %revision-date?', array('%revision-date' => format_date($type_revision->revision_timestamp))), 'admin/bat/config/types/manage/' . $type_revision->type_id . '/revisions', t('This action cannot be undone.'), t('Delete'), t('Cancel'));
}

/**
 * Form submission handler for bat_type_revision_delete_confirm().
 */
function bat_type_revision_delete_confirm_submit($form, &$form_state) {
  $type_revision = $form['#type_revision'];

  bat_type_revisioning_event('pre delete', $type_revision);

  entity_revision_delete('bat_type', $type_revision->revision_id);

  bat_type_revisioning_event('post delete', $type_revision);

  $form_state['redirect'] = 'admin/bat/config/types/manage/' . $type_revision->type_id . '/revisions';
}
