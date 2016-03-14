<?php

/**
 * @file
 * BatTypeBundle editing UI.
 */

/**
 * UI controller.
 */
class BatTypeBundleUIController extends EntityDefaultUIController {

  /**
   * Overrides hook_menu() defaults.
   */
  public function hook_menu() {
    $items = parent::hook_menu();

    foreach ($items as &$item) {
      unset($item['access callback']);
      $item['access arguments'] = array('administer bat_type_bundle entities');
    }
    $items[$this->path]['description'] = 'Manage type bundles, including adding and removing fields and the display of fields.';
    $items[$this->path]['weight'] = '3';

    return $items;
  }
}

/**
 * Generates the unit bundle editing form.
 */
function bat_type_bundle_form($form, &$form_state, $type_bundle, $op = 'edit') {

  $form['#attributes']['class'][] = 'bat-management-form bat-unit-bundle-edit-form';
  $form['#attached']['css'] = array(
    drupal_get_path('module', 'bat_type') . '/css/bat_type_bundle.css',
    drupal_get_path('module', 'bat_ui') . '/css/bat_ui.css',
  );

  if ($op == 'clone') {
    $type_bundle->label .= ' (cloned)';
    $type_bundle->type = '';
  }

  $form['label'] = array(
    '#title' => t('Type bundle name'),
    '#type' => 'textfield',
    '#default_value' => $type_bundle->label,
    '#description' => t('The human-readable name of this type bundle.'),
    '#required' => TRUE,
    '#size' => 30,
    '#weight' => -100,
  );

  // Machine-readable type name.
  $form['type'] = array(
    '#type' => 'machine_name',
    '#default_value' => isset($type_bundle->type) ? $type_bundle->type : '',
    '#maxlength' => 32,
    '#machine_name' => array(
      'exists' => 'bat_unit_get_type_bundles',
      'source' => array('label'),
    ),
    '#description' => t('A unique machine-readable name for this type bundle. It must only contain lowercase letters, numbers, and underscores.'),
    '#weight' => -99,
  );

  if ($op == 'edit') {
    $form['type']['#disabled'] = TRUE;
  }

  // Add the field related form elements.
  $form_state['bat_type_bundle'] = $type_bundle;
  field_attach_form('bat_type_bundle', $type_bundle, $form, $form_state);

  $form['additional_settings'] = array(
    '#type' => 'vertical_tabs',
    '#weight' => 99,
  );

  $form['actions'] = array(
    '#type' => 'actions',
    '#tree' => FALSE,
  );

  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Bat Type bundle'),
    '#weight' => 100,
    '#submit' => array('bat_type_bundle_form_submit'),
  );

  return $form;
}

/**
 * Validate callback for bat_type_bundle_form form.
 *
 * @see bat_type_bundle_form()
 */
function bat_type_bundle_form_validate(&$form, &$form_state) {
  // Notify field widgets to validate their data.
  entity_form_field_validate('bat_type_bundle', $form, $form_state);
}

/**
 * Form API submit callback for the unit bundle form.
 */
function bat_type_bundle_form_submit($form, &$form_state) {
  $type_bundle = entity_ui_form_submit_build_entity($form, $form_state);
  // Save and go back.
  $type_bundle->save();

  $form_state['type_bundle'] = $type_bundle;

  $form_state['redirect'] = 'admin/bat/config/type-bundles';
}

/**
 * Form API submit callback for the delete button.
 */
function bat_type_bundle_form_submit_delete(&$form, &$form_state) {
  $destination = array();
  if (isset($_GET['destination'])) {
    $destination = drupal_get_destination();
    unset($_GET['destination']);
  }

  $form_state['redirect'] = array('admin/bat/types/type_bundles/manage/' . $form_state['bat_type_bundle']->type . '/delete', array('query' => $destination));
}
