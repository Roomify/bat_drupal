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

  /**
   * {@inheritdoc}
   */
  protected function overviewTableRow($conditions, $id, $entity, $additional_cols = array()) {
    $row = parent::overviewTableRow($conditions, $id, $entity, $additional_cols);
    $row[] = l(t('manage states'), $this->path . '/manage/' . $id . '/states');

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  protected function operationCount() {
    return parent::operationCount() + 1;
  }

}

/**
 * Generates the Event type editing form.
 */
function bat_event_type_form($form, &$form_state, $event_type, $op = 'edit') {
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
  elseif ($op == 'edit') {
    $form['type']['#disabled'] = TRUE;
  }

  $form['event_granularity'] = array(
    '#type' => 'select',
    '#title' => t('Event Granularity'),
    '#options' => array('bat_daily' => t('Daily'), 'bat_hourly' => t('Hourly')),
    '#default_value' => isset($event_type->event_granularity) ? $event_type->event_granularity : 'bat_daily',
  );

  if (isset($event_type->is_new)) {
    // Check for available Target Entity types.
    $target_entity_types = module_invoke_all('bat_event_target_entity_types');
    if (count($target_entity_types) == 1) {
      // If there's only one target entity type, we simply store the value
      // without showing it to the user.
      $form['target_entity_type'] = array(
        '#type' => 'value',
        '#value' => $target_entity_types[0],
      );
    }
    else {
      // Build option list.
      $options = array();
      foreach ($target_entity_types as $target_entity_type) {
        $target_entity_info = entity_get_info($target_entity_type);
        $options[$target_entity_type] = $target_entity_info['label'];
      }
      $form['target_entity_type'] = array(
        '#type' => 'select',
        '#title' => t('Target Entity Type'),
        '#description' => t('Select the target entity type for this Event type. In most cases you will wish to leave this as "Unit".'),
        '#options' => $options,
        // Default to BAT Unit if available.
        '#default_value' => isset($target_entity_types['bat_unit']) ? 'bat_unit' : '',
      );
    }
  }

  if (!isset($event_type->is_new) && $event_type->fixed_event_states == 0) {
    $fields_options = array();

    $fields = field_info_instances('bat_event', $event_type->type);

    foreach ($fields as $field) {
      $fields_options[$field['field_name']] = $field['field_name'];
    }

    $form['events'] = array(
      '#type' => 'fieldset',
      '#group' => 'additional_settings',
      '#title' => t('Events'),
      '#tree' => TRUE,
      '#weight' => 80,
    );

    $form['events'][$event_type->type] = array(
      '#type' => 'select',
      '#title' => t('Select your default @event field', array('@event' => $event_type->label)),
      '#options' => $fields_options,
      '#default_value' => isset($event_type->default_event_value_field_ids[$event_type->type]) ? $event_type->default_event_value_field_ids[$event_type->type] : NULL,
      '#empty_option' => t('- Select a field -'),
    );
  }

  if (!isset($event_type->is_new)) {
    $fields_options = array();
    $fields = field_info_instances('bat_event', $event_type->type);

    foreach ($fields as $field) {
      $fields_options[$field['field_name']] = $field['field_name'];
    }

    $form['event_label'] = array(
      '#type' => 'fieldset',
      '#group' => 'additional_settings',
      '#title' => t('Label Source'),
      '#tree' => TRUE,
      '#weight' => 80,
    );

    $form['event_label']['default_event_label_field_name'] = array(
      '#type' => 'select',
      '#title' => t('Select your label field', array('@event' => $event_type->label)),
      '#default_value' => isset($event_type->default_event_label_field_name) ? $event_type->default_event_label_field_name : NULL,
      '#empty_option' => t('- Select a field -'),
      '#description' => t('If you select a field here, its value will be used as the label for your event. BAT will fall back to using the event state as the label if the field has no value.'),
      '#options' => $fields_options,
    );
  }

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
  if (isset($form_state['values']['events'][$form_state['values']['type']])) {
    foreach ($form_state['values']['events'] as $event => $field) {
      $form_state['bat_event_type']->default_event_value_field_ids[$event] = $field;
    }
  }
  if (isset($form_state['values']['event_label']['default_event_label_field_name'])) {
    $form_state['bat_event_type']->default_event_label_field_name = $form_state['values']['event_label']['default_event_label_field_name'];
  }

  $event_type = entity_ui_form_submit_build_entity($form, $form_state);
  // Save and go back.
  $event_type->save();
  $form_state['redirect'] = 'admin/bat/events/event-types';
}

/**
 * Form API submit callback for the delete button.
 */
function bat_event_type_form_submit_delete(&$form, &$form_state) {
  $destination = array();
  if (isset($_GET['destination'])) {
    $destination = drupal_get_destination();
    unset($_GET['destination']);
  }

  $form_state['redirect'] = array('admin/bat/events/event_types/manage/' . $form_state['bat_event_type']->type . '/delete', array('query' => $destination));
}
