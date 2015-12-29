<?php

/**
 * @file
 * BatUnit type editing UI.
 */

/**
 * UI controller.
 */
class BatUnitTypeUIController extends EntityDefaultUIController {

  /**
   * Overrides hook_menu() defaults.
   */
  public function hook_menu() {
    $items = parent::hook_menu();

    foreach ($items as &$item) {
      unset($item['access callback']);
      $item['access arguments'] = array('administer bat_unit_type entities');
    }
    $items[$this->path]['description'] = 'Manage unit types, including adding and removing fields and the display of fields.';
    $items[$this->path]['weight'] = '3';
    $items[$this->path]['type'] = MENU_LOCAL_TASK;

    return $items;
  }
}

/**
 * Generates the unit type editing form.
 */
function bat_unit_type_form($form, &$form_state, $unit_type, $op = 'edit') {

  $form['#attributes']['class'][] = 'bat-management-form bat-unit-type-edit-form';
  $form['#attached']['css'] = array(
    drupal_get_path('module', 'bat_unit') . '/css/bat_unit_type.css',
    drupal_get_path('module', 'bat_ui') . '/css/bat_ui.css',
  );

  if ($op == 'clone') {
    $unit_type->label .= ' (cloned)';
    $unit_type->type = '';
  }

  $form['label'] = array(
    '#title' => t('Unit type name'),
    '#type' => 'textfield',
    '#default_value' => $unit_type->label,
    '#description' => t('The human-readable name of this unit type.'),
    '#required' => TRUE,
    '#size' => 30,
    '#weight' => -100,
  );

  // Machine-readable type name.
  $form['type'] = array(
    '#type' => 'machine_name',
    '#default_value' => isset($unit_type->type) ? $unit_type->type : '',
    '#maxlength' => 32,
    '#machine_name' => array(
      'exists' => 'bat_unit_get_types',
      'source' => array('label'),
    ),
    '#description' => t('A unique machine-readable name for this unit type. It must only contain lowercase letters, numbers, and underscores.'),
    '#weight' => -99,
  );

  if ($op == 'edit') {
    $form['type']['#disabled'] = TRUE;
  }

  // Add the field related form elements.
  $form_state['bat_unit_type'] = $unit_type;
  field_attach_form('bat_unit_type', $unit_type, $form, $form_state);

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
    '#value' => t('Save unit type'),
    '#weight' => 100,
    '#submit' => array('bat_unit_type_form_submit'),
  );

  return $form;
}

/**
 * Validate callback for bat_unit_type_form form.
 *
 * @see bat_unit_type_form()
 */
function bat_unit_type_form_validate(&$form, &$form_state) {
  // Notify field widgets to validate their data.
  entity_form_field_validate('bat_unit_type', $form, $form_state);
}

/**
 * Form API submit callback for the unit type form.
 */
function bat_unit_type_form_submit($form, &$form_state) {
  $unit_type = entity_ui_form_submit_build_entity($form, $form_state);
  // Save and go back.
  $unit_type->save();

  $form_state['unit_type'] = $unit_type;

  $form_state['redirect'] = 'admin/bat/units/unit-types';
}

/**
 * Form API submit callback for the delete button.
 */
function bat_unit_type_form_submit_delete(&$form, &$form_state) {
  $form_state['redirect'] = 'admin/bat/units/unit_types/manage/' . $form_state['bat_unit_type']->type . '/delete';
}
