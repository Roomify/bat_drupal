<?php
/**
 * @file
 * unit_type editing UI.
 *
 * We make very little use of the EntityAPI interface for this - preferring
 * instead to use views. That offers more flexibility to change a UI that will,
 * more often than not, be end-user facing.
 */

/**
 * UI controller.
 */
class BatUnitTypeUIController extends EntityDefaultUIController {
  /**
   * Overrides hook_menu() defaults. Main reason for doing this is that
   * parent class hook_menu() is optimized for entity type administration.
   */
  public function hook_menu() {
    $items = array();
    $id_count = count(explode('/', $this->path));
    $wildcard = isset($this->entityInfo['admin ui']['menu wildcard']) ? $this->entityInfo['admin ui']['menu wildcard'] : '%' . $this->entityType;
    $items[$this->path] = array(
      'title' => 'Unit Types',
      'description' => 'Add, edit, and update Unit Types.',
      'page callback' => 'system_admin_menu_block_page',
      'access arguments' => array('access unit_type overview'),
      'file path' => drupal_get_path('module', 'system'),
      'file' => 'system.admin.inc',
      'weight' => 10,
    );

    // Change the add page menu to multiple types of entities.
    $items[$this->path . '/add'] = array(
      'title' => 'Add a Unit Type',
      'description' => 'Create a new Unit Type.',
      'page callback'  => 'bat_unit_type_add_page',
      'access callback'  => '_bat_unit_type_add_access',
      'type' => MENU_NORMAL_ITEM,
      'weight' => 20,
      'file' => 'bat_unit_type.admin.inc',
      'file path' => drupal_get_path('module', $this->entityInfo['module']),
    );

    // Add menu items to add each different bundle of unit types.
    foreach (bat_unit_get_type_bundles() as $bundle) {
      $items[$this->path . '/add/' . $bundle->type] = array(
        'title' => 'Add @unit_type_bundle_label unit_type',
        'title arguments' => array('@unit_type_bundle_label' => $bundle->label),
        'page callback' => 'bat_unit_type_create_form_wrapper',
        'page arguments' => array($bundle->type),
        'access callback' => 'bat_unit_type_bundle_access',
        'access arguments' => array('create', bat_unit_type_create(array('type' => $bundle->type, 'uid' => 0))),
        'file' => 'bat_unit_type.admin.inc',
        'file path' => drupal_get_path('module', $this->entityInfo['module']),
      );
    }

    // Loading and editing unit_type entities.
    $items[$this->path . '/unit_type/' . $wildcard] = array(
      'title callback' => 'bat_unit_type_page_title',
      'title arguments' => array($id_count + 1),
      'page callback' => 'bat_unit_type_page_view',
      'page arguments' => array($id_count + 1),
      'access callback' => 'bat_unit_type_access',
      'access arguments' => array('view', $id_count + 1),
      'context' => MENU_CONTEXT_PAGE | MENU_CONTEXT_INLINE,
    );

    $items[$this->path . '/unit_type/' . $wildcard . '/view'] = array(
      'title' => 'View',
      'type' => MENU_DEFAULT_LOCAL_TASK,
      'weight' => -10,
      'context' => MENU_CONTEXT_PAGE | MENU_CONTEXT_INLINE,
    );

    $items[$this->path . '/unit_type/' . $wildcard . '/edit'] = array(
      'title' => 'Edit',
      'page callback' => 'bat_unit_type_form_wrapper',
      'page arguments' => array($id_count + 1),
      'access callback' => 'bat_unit_type_access',
      'access arguments' => array('update', $id_count + 1),
      'weight' => 0,
      'type' => MENU_LOCAL_TASK,
      'context' => MENU_CONTEXT_PAGE | MENU_CONTEXT_INLINE,
      'file' => 'bat_unit_type.admin.inc',
      'file path' => drupal_get_path('module', $this->entityInfo['module']),
    );

    $items[$this->path . '/unit_type/' . $wildcard . '/delete'] = array(
      'title' => 'Delete',
      'page callback' => 'bat_unit_type_delete_form_wrapper',
      'page arguments' => array($id_count + 1),
      'access callback' => 'bat_unit_type_access',
      'access arguments' => array('delete', $id_count + 1),
      'type' => MENU_LOCAL_TASK,
      'context' => MENU_CONTEXT_INLINE,
      'weight' => 10,
      'file' => 'bat_unit_type.admin.inc',
      'file path' => drupal_get_path('module', $this->entityInfo['module']),
    );

    // Menu item for viewing unit_types.
    $items['unit_type/' . $wildcard] = array(
      'title callback' => 'bat_unit_type_page_title',
      'title arguments' => array(1),
      'page callback' => 'bat_unit_type_page_view',
      'page arguments' => array(1),
      'access callback' => 'bat_unit_type_access',
      'access arguments' => array('view', 1),
      'type' => MENU_CALLBACK,
    );
    return $items;
  }

  /**
   * Creates the markup for the add unit type Entities page within the class
   * so it can easily be extended/overridden.
   */
  public function addPage() {
    $item = menu_get_item();
    $content = system_admin_menu_block($item);
    if (count($content) == 1) {
      $item = array_shift($content);
      drupal_goto($item['href']);
    }
    return array(
      '#theme' => 'bat_unit_type_add_list',
      '#content' => $content,
    );
  }
}

/**
 * Form callback wrapper: edit a  unit_type.
 *
 * @param $unit_type
 *   The Batunit_type object being edited by this form.
 *
 * @see bat_unit_type_edit_form()
 */
function bat_unit_type_form_wrapper($unit_type) {
  // Add the breadcrumb for the form's location.
  bat_unit_type_type_set_breadcrumb();
  drupal_set_title(t('Edit @unit_type_name', array('@unit_type_name' => $unit_type->name)));
  $unit_type->date = format_date($unit_type->created, 'custom', 'Y-m-d H:i:s O');
  $account = user_load($unit_type->uid);
  $unit_type->author_name = isset($account->name) ? $account->name : '';
  return drupal_get_form('bat_unit_type_edit_form', $unit_type);
}
/**
 * Form callback wrapper: create a unit_type.
 *
 * @param $type
 *   The unit_type type for the unit_type to be created.
 */
function bat_unit_type_create_form_wrapper($type) {
  global $user;

  // Add the breadcrumb for the form's location.
  bat_unit_type_type_set_breadcrumb();
  $unit_type = bat_unit_type_create(array('type' => $type, 'uid' => $user->uid));
  $unit_type->created = REQUEST_TIME;
  $unit_type->author_name = $user->name;
  $unit_type->status = 1;
  return drupal_get_form('bat_unit_type_edit_form', $unit_type);
}
/**
 * Form callback wrapper: delete a unit_type.
 *
 * @param BatUnitType $unit_type
 *   The unit_type object being edited by this form.
 *
 * @see bat_unit_type_edit_form()
 */
function bat_unit_type_delete_form_wrapper($unit_type) {
  // Add the breadcrumb for the form's location.
  bat_unit_type_type_set_breadcrumb();
  return drupal_get_form('bat_unit_type_delete_form', $unit_type);
}
/**
 * Form callback: create or edit a unit_type.
 *
 * @param BatUnitType $unit_type
 *   The BatUnitType object to edit or, for a creation form, an empty unit_type object
 *   with only a unit_type bundle defined.
 */
function bat_unit_type_edit_form($form, &$form_state, $unit_type) {
  $form['#attributes']['class'][] = 'bat-management-form bat-unit_type-edit-form';
  $form['#attached']['css'] = array(
    drupal_get_path('module', 'bat_unit_type') . '/css/bat_unit_type.css',
    drupal_get_path('module', 'bat_ui') . '/css/bat_ui.css',
  );
  $form['type'] = array(
    '#type' => 'value',
    '#value' => $unit_type->type,
  );

  // Add the default field elements.
  $form['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Unit Type name'),
    '#default_value' => isset($unit_type->name) ? $unit_type->name : '',
    '#maxlength' => 255,
    '#required' => TRUE,
    '#weight' => -99,
  );

  // Add the field related form elements.
  $form_state['bat_unit_type'] = $unit_type;
  field_attach_form('bat_unit_type', $unit_type, $form, $form_state, isset($unit_type->language)? $unit_type->language : NULL);
  $form['additional_settings'] = array(
    '#type' => 'vertical_tabs',
    '#weight' => 99,
  );

  // Unit Type author information for administrators.
  $form['author'] = array(
    '#type' => 'fieldset',
    '#access' => user_access('bypass bat_unit_type entities access'),
    '#title' => t('Authoring information'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#group' => 'additional_settings',
    '#attributes' => array(
      'class' => array('unit_type-form-author'),
    ),
    '#attached' => array(
      'js' => array(
        drupal_get_path('module', 'bat_unit_type') . '/js/bat_unit_type.js',
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
    '#default_value' => !empty($unit_type->author_name) ? $unit_type->author_name : '',
    '#weight' => -1,
    '#description' => t('Leave blank for %anonymous.', array('%anonymous' => variable_get('anonymous', t('Anonymous')))),
  );
  $form['author']['date'] = array(
    '#type' => 'textfield',
    '#title' => t('Authored on'),
    '#maxlength' => 25,
    '#description' => t('Format: %time. The date format is YYYY-MM-DD and %timezone is the time zone offset from UTC. Leave blank to use the time of form submission.', array('%time' => !empty($unit_type->date) ? date_format(date_create($unit_type->date), 'Y-m-d H:i:s O') : format_date($unit_type->created, 'custom', 'Y-m-d H:i:s O'), '%timezone' => !empty($unit_type->date) ? date_format(date_create($unit_type->date), 'O') : format_date($unit_type->created, 'custom', 'O'))),
    '#default_value' => !empty($unit_type->date) ? $unit_type->date : '',
  );

  // Unit Type publishing options for administrators.
  $form['options'] = array(
    '#type' => 'fieldset',
    '#access' => user_access('bypass bat_unit_type entities access'),
    '#title' => t('Publishing options'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#group' => 'additional_settings',
    '#attributes' => array(
      'class' => array('unit_type-form-published'),
    ),
    '#attached' => array(
      'js' => array(drupal_get_path('module', 'bat_unit_type') . '/js/bat_unit_type.js'),
    ),
    '#weight' => 95,
  );
  $form['options']['status'] = array(
    '#type' => 'checkbox',
    '#title' => t('Published'),
    '#default_value' => $unit_type->status,
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
    '#value' => t('Save Unit Type'),
    '#submit' => $submit + array('bat_unit_type_edit_form_submit'),
  );
  if (!empty($unit_type->name) && bat_unit_type_access('delete', $unit_type)) {
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete Unit Type'),
      '#suffix' => l(t('Cancel'), 'admin/bat/config/unit-types'),
      '#submit' => $submit + array('bat_unit_type_form_submit_delete'),
      '#weight' => 45,
    );
  }
  // We append the validate handler to #validate in case a form callback_wrapper
  // is used to add validate handlers earlier.
  $form['#validate'][] = 'bat_unit_type_edit_form_validate';
  return $form;
}

/**
 * Form API validate callback for the booking unit_type form.
 */
function bat_unit_type_edit_form_validate(&$form, &$form_state) {
  // Notify field widgets to validate their data.
  entity_form_field_validate('bat_unit_type', $form, $form_state);
}

/**
 * Form API submit callback for the unit_type form.
 */
function bat_unit_type_edit_form_submit(&$form, &$form_state) {
  $unit_type = entity_ui_controller('bat_unit_type')->entityFormSubmitBuildEntity($form, $form_state);
  $unit_type->created = !empty($unit->date) ? strtotime($unit->date) : REQUEST_TIME;

  if ($unit_type->unit_type_id !== '') {
    $unit_type->changed = time();
  }

  $unit_type->save();
  drupal_set_message(t('Unit Type @name saved', array('@name' => $unit_type->name)));

  $form_state['redirect'] = 'admin/bat/config/unit-types';
}

/**
 * Form API submit callback for the delete button.
 */
function bat_unit_type_form_submit_delete(&$form, &$form_state) {
  $form_state['redirect'] = 'admin/bat/config/unit-types/unit-type/' . $form_state['bat_unit_type']->unit_type_id . '/delete';
}

/**
 * Form callback: confirmation form for deleting a Unit Type.
 *
 * @param $unit_type
 *   The Unit Type entity to delete.
 *
 * @see confirm_form()
 */
function bat_unit_type_delete_form($form, &$form_state, $unit_type) {
  $form_state['bat_unit_type'] = $unit_type;
  $form['#submit'][] = 'bat_unit_type_delete_form_submit';
  $form = confirm_form($form,
    t('Are you sure you want to delete Unit Type %name?', array('%name' => $unit_type->name)),
    'admin/bat/config/unit-types/unit-type',
    '<p>' . t('This action cannot be undone.') . '</p>',
    t('Delete'),
    t('Cancel'),
    'confirm'
  );
  return $form;
}

/**
 * Submit callback for unit_type_delete_form
 */
function bat_unit_type_delete_form_submit($form, &$form_state) {
  $unit_type = $form_state['bat_unit_type'];
  bat_unit_type_delete($unit_type);
  drupal_set_message(t('The Unit Type %name has been deleted.', array('%name' => $unit_type->name)));
  watchdog('bat', 'Deleted Unit Type %name.', array('%name' => $unit_type->name));
  $form_state['redirect'] = 'admin/bat/config/unit-types';
}

/**
 * Page to add Unit Types.
 */
function bat_unit_type_add_page() {
  $controller = entity_ui_controller('bat_unit_type');
  return $controller->addPage();
}

/**
 * Displays the list of available Unit Type bundles for Unit Type creation.
 *
 * @ingroup themeable
 */
function theme_bat_unit_type_add_list($variables) {
  $content = $variables['content'];
  $output = '';
  if ($content) {
    $output = '<dl class="unit_type-type-list">';
    foreach ($content as $item) {
      $output .= '<dt>' . l($item['title'], $item['href']) . '</dt>';
      $output .= '<dd>' . filter_xss_admin($item['description']) . '</dd>';
    }
    $output .= '</dl>';
  }
  else {
    if (user_access('administer unit_type bundles')) {
      $output = '<p>' . t('Unit Type cannot be added because you have not created any Unit Type bundles yet. Go to the <a href="@create-unit_type_bundle">unit_type bundle creation page</a> to add a new unit_type type.', array('@create-unit_type_bundle' => url('admin/bat/config/unit-type-bundles/add'))) . '</p>';
    }
    else {
      $output = '<p>' . t('No Unit Types have been created yet.') . '</p>';
    }
  }
  return $output;
}

/**
 * Sets the breadcrumb for administrative BAT pages.
 */
function bat_unit_type_type_set_breadcrumb() {
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    l(t('Administration'), 'admin'),
    l(t('bat'), 'admin/bat'),
    l(t('bat'), 'admin/bat/config'),
    l(t('bat'), 'admin/bat/config/unit_type-types'),
  );
  drupal_set_breadcrumb($breadcrumb);
}
