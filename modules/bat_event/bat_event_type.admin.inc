<?php

/**
 * @file
 * BatEvent type editing UI.
 */

/**
 * UI controller.
 */
class BatEventTypeUIController extends EntityDefaultUIController {

  /**
   * Overrides hook_menu() defaults.
   */
  public function hook_menu() {
    $items = parent::hook_menu();
    foreach ($items as &$item) {
      unset($item['access callback']);
      $item['access arguments'] = array('administer bat_event_type entities');
    }
    $items[$this->path]['description'] = 'Manage event types, including adding and removing fields and the display of fields.';
    $items[$this->path]['weight'] = '3';
    $items[$this->path]['type'] = MENU_LOCAL_TASK;

    return $items;
  }

  protected function overviewTableRow($conditions, $id, $entity, $additional_cols = array()) {
    $row = parent::overviewTableRow($conditions, $id, $entity, $additional_cols);
    $row[] = l(t('manage states'), $this->path . '/manage/' . $id . '/states');

    return $row;
  }

  protected function operationCount() {
    return parent::operationCount() + 1;
  }

}

/**
 * Generates the Event type editing form.
 */
function bat_event_type_form($form, &$form_state, $event_type, $op = 'edit') {

  $form['#attacched']['css'][] = drupal_get_path('module', 'bat_ui') . '/css/bat_ui.css';
  $form['#attributes']['class'][] = 'bat-management-form bat-event-type-form';

  if ($op == 'clone') {
    $event_type->label .= ' (cloned)';
    $event_type->type = '';
  }

  $form['label'] = array(
    '#title' => t('Label'),
    '#type' => 'textfield',
    '#default_value' => $event_type->label,
    '#description' => t('The human-readable name of this event type.'),
    '#required' => TRUE,
    '#size' => 30,
  );

  // Machine-readable type name.
  $form['type'] = array(
    '#type' => 'machine_name',
    '#default_value' => isset($event_type->type) ? $event_type->type : '',
    '#maxlength' => 32,
    '#machine_name' => array(
      'exists' => 'bat_event_get_types',
      'source' => array('label'),
    ),
    '#description' => t('A unique machine-readable name for this event type. It must only contain lowercase letters, numbers, and underscores.'),
  );

  if ($op == 'add') {
    $form['fixed_event_states'] = array(
      '#type' => 'checkbox',
      '#title' => t('Fixed event states'),
    );
  }

  $form['event_granularity'] = array(
    '#type' => 'select',
    '#title' => t('Event Granularity'),
    '#options' => array('daily' => t('Daily'), 'hourly' => t('Hourly')),
    '#default_value' => isset($event_type->event_granularity) ? $event_type->event_granularity : 'daily',
  );

  $form['actions'] = array(
    '#type' => 'actions',
    '#tree' => FALSE,
  );
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Event type'),
    '#weight' => 40,
    '#submit' => array('bat_event_type_form_submit'),
  );

  return $form;
}

/**
 * Form API submit callback for the event type form.
 */
function bat_event_type_form_submit(&$form, &$form_state) {
  $event_type = entity_ui_form_submit_build_entity($form, $form_state);
  // Save and go back.
  $event_type->save();
  $form_state['redirect'] = 'admin/bat/events/event-types';
}

/**
 * Form API submit callback for the delete button.
 */
function bat_event_type_form_submit_delete(&$form, &$form_state) {
  $form_state['redirect'] = 'admin/bat/events/event_types/manage/' . $form_state['bat_event_type']->type . '/delete';
}
