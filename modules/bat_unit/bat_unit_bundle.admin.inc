<?php

/**
 * @file
 * BatUnitBundle editing UI.
 */

/**
 * UI controller.
 */
class BatUnitBundleUIController extends EntityDefaultUIController {

  /**
   * Overrides hook_menu() defaults.
   */
  public function hook_menu() {
    $items = parent::hook_menu();

    foreach ($items as &$item) {
      unset($item['access callback']);
      $item['access arguments'] = array('administer bat_unit_bundle entities');
    }
    $items[$this->path]['description'] = 'Manage unit bundles, including adding and removing fields and the display of fields.';
    $items[$this->path]['weight'] = '3';

    return $items;
  }

}

/**
 * Generates the unit bundle editing form.
 */
function bat_unit_bundle_form($form, &$form_state, $unit_bundle, $op = 'edit') {
  $form['#attributes']['class'][] = 'bat-management-form bat-unit-bundle-edit-form';

  if ($op == 'clone') {
    $unit_bundle->label .= ' (cloned)';
    $unit_bundle->type = '';
  }

  $form['label'] = array(
    '#title' => t('Unit bundle name'),
    '#type' => 'textfield',
    '#default_value' => $unit_bundle->label,
    '#description' => t('The human-readable name of this unit bundle.'),
    '#required' => TRUE,
    '#size' => 30,
    '#weight' => -100,
  );

  // Machine-readable type name.
  $form['type'] = array(
    '#type' => 'machine_name',
    '#default_value' => isset($unit_bundle->type) ? $unit_bundle->type : '',
    '#maxlength' => 32,
    '#machine_name' => array(
      'exists' => 'bat_unit_get_bundles',
      'source' => array('label'),
    ),
    '#description' => t('A unique machine-readable name for this unit bundle. It must only contain lowercase letters, numbers, and underscores.'),
    '#weight' => -99,
  );

  if ($op == 'edit') {
    $form['type']['#disabled'] = TRUE;
  }

  // Add the field related form elements.
  $form_state['bat_unit_bundle'] = $unit_bundle;
  field_attach_form('bat_unit_bundle', $unit_bundle, $form, $form_state);

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
    '#value' => t('Save unit bundle'),
    '#weight' => 100,
    '#submit' => array('bat_unit_bundle_form_submit'),
  );

  return $form;
}

/**
 * Validate callback for bat_unit_bundle_form form.
 *
 * @see bat_unit_bundle_form()
 */
function bat_unit_bundle_form_validate(&$form, &$form_state) {
  // Notify field widgets to validate their data.
  entity_form_field_validate('bat_unit_bundle', $form, $form_state);
}

/**
 * Form API submit callback for the unit bundle form.
 */
function bat_unit_bundle_form_submit($form, &$form_state) {
  $unit_bundle = entity_ui_form_submit_build_entity($form, $form_state);
  // Save and go back.
  $unit_bundle->save();

  $form_state['unit_bundle'] = $unit_bundle;

  $form_state['redirect'] = 'admin/bat/config/unit-bundles';
}

/**
 * Form API submit callback for the delete button.
 */
function bat_unit_bundle_form_submit_delete(&$form, &$form_state) {
  $destination = array();
  if (isset($_GET['destination'])) {
    $destination = drupal_get_destination();
    unset($_GET['destination']);
  }

  $form_state['redirect'] = array('admin/bat/units/unit_bundles/manage/' . $form_state['bat_unit_bundle']->type . '/delete', array('query' => $destination));
}
