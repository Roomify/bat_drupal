<?php

/**
 * @file
 * BatBooking type editing UI.
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
    $items[$this->path]['description'] = 'Manage booking types, including adding and removing fields and the display of fields.';
    $items[$this->path]['weight'] = '3';
    $items[$this->path]['type'] = MENU_LOCAL_TASK;
    return $items;
  }
}

/**
 * Generates the Booking type editing form.
 */
function bat_booking_type_form($form, &$form_state, $booking_type, $op = 'edit') {

  $form['#attributes']['class'][] = 'bat-management-form bat-booking-type-form';

  if ($op == 'clone') {
    $booking_type->label .= ' (cloned)';
    $booking_type->type = '';
  }

  $form['label'] = array(
    '#title' => t('Label'),
    '#type' => 'textfield',
    '#default_value' => $booking_type->label,
    '#description' => t('The human-readable name of this booking type.'),
    '#required' => TRUE,
    '#size' => 30,
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
  );

  $form['actions'] = array(
    '#type' => 'actions',
    '#tree' => FALSE,
  );
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Booking type'),
    '#weight' => 40,
    '#submit' => array('bat_booking_type_form_submit'),
  );

  return $form;
}

/**
 * Form API submit callback for the booking type form.
 */
function bat_booking_type_form_submit(&$form, &$form_state) {
  $booking_type = entity_ui_form_submit_build_entity($form, $form_state);
  // Save and go back.
  $booking_type->save();
  $form_state['redirect'] = 'admin/bat/bookings/booking-types';
}

/**
 * Form API submit callback for the delete button.
 */
function bat_booking_type_form_submit_delete(&$form, &$form_state) {
  $form_state['redirect'] = 'admin/bat/bookings/booking_types/manage/' . $form_state['bat_booking_type']->type . '/delete';
}
