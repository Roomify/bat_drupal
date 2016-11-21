<?php

/**
 * @file
 * BatBookingType editing UI.
 */

/**
 * UI controller.
 */
class BatBookingTypeUIController extends EntityDefaultUIController {

  /**
   * Overrides hook_menu() defaults.
   */
  public function hook_menu() {
    $items = parent::hook_menu();

    foreach ($items as &$item) {
      unset($item['access callback']);
      $item['access arguments'] = array('administer bat_booking_type entities');
    }

    return $items;
  }

}

/**
 * Generates the booking type editing form.
 */
function bat_booking_type_form($form, &$form_state, $booking_type, $op = 'edit') {
  if ($op == 'clone') {
    $booking_type->label .= ' (cloned)';
    $booking_type->type = '';
  }

  $form['label'] = array(
    '#title' => t('Booking type name'),
    '#type' => 'textfield',
    '#default_value' => $booking_type->label,
    '#description' => t('The human-readable name of this booking type.'),
    '#required' => TRUE,
    '#size' => 30,
    '#weight' => -100,
  );

  // Machine-readable type name.
  $form['type'] = array(
    '#type' => 'machine_name',
    '#default_value' => isset($booking_type->type) ? $booking_type->type : '',
    '#maxlength' => 32,
    '#machine_name' => array(
      'exists' => 'bat_booking_get_types',
      'source' => array('label'),
    ),
    '#description' => t('A unique machine-readable name for this booking type. It must only contain lowercase letters, numbers, and underscores.'),
    '#weight' => -99,
  );

  if ($op == 'edit') {
    $form['type']['#disabled'] = TRUE;
  }

  // Add the field related form elements.
  $form_state['bat_booking_type'] = $booking_type;
  field_attach_form('bat_booking_type', $booking_type, $form, $form_state);

  $form['additional_settings'] = array(
    '#type' => 'vertical_tabs',
    '#weight' => 99,
  );

  if (!isset($booking_type->is_new)) {
    $fields_options = array();
    $fields = field_info_instances('bat_booking', $booking_type->type);

    foreach ($fields as $field) {
      $field_info = field_info_field($field['field_name']);
      $fields_options[$field['field_name']] = $field['field_name'];
    }

    $form['event_label'] = array(
      '#type' => 'fieldset',
      '#group' => 'additional_settings',
      '#title' => t('Label Source'),
      '#tree' => TRUE,
      '#weight' => 80,
    );

    $form['event_label']['default_booking_label_field_name'] = array(
      '#type' => 'select',
      '#title' => t('Select your label field', array('@booking' => $booking_type->label)),
      '#default_value' => isset($booking_type->default_booking_label_field_name) ? $booking_type->default_booking_label_field_name : NULL,
      '#empty_option' => t('- Select a field -'),
      '#description' => t('If you select a field here, its value will be used as the label for your booking. BAT will fall back to using the property name as the label if the field has no value.'),
      '#options' => $fields_options,
    );
  }

  $form['actions'] = array(
    '#type' => 'actions',
    '#tree' => FALSE,
  );

  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save booking type'),
    '#weight' => 100,
    '#submit' => array('bat_booking_type_form_submit'),
  );

  $form['#validate'][] = 'bat_booking_type_form_validate';
  return $form;
}

/**
 * Form API validation callback for the booking type form.
 */
function bat_booking_type_form_validate(&$form, &$form_state) {
  // Notify field widgets to validate their data.
  entity_form_field_validate('bat_booking_type', $form, $form_state);
}

/**
 * Form API submit callback for the booking type form.
 */
function bat_booking_type_form_submit(&$form, &$form_state) {
  if (isset($form_state['values']['event_label']['default_booking_label_field_name'])) {
    $form_state['bat_booking_type']->default_booking_label_field_name = $form_state['values']['event_label']['default_booking_label_field_name'];
  }

  $booking_type = entity_ui_controller('bat_booking_type')->entityFormSubmitBuildEntity($form, $form_state);
  // Save and go back.
  $booking_type->save();

  $form_state['booking_type'] = $booking_type;

  $form_state['redirect'] = 'admin/bat/config/booking-types';
}
