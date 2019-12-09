<?php

/**
 * @file
 * Contains \Drupal\bat_event_ui\Form\BatEventUiBulkUpdateForm.
 */

namespace Drupal\bat_event_ui\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class BatEventUiBulkUpdateForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bat_event_ui_bulk_update_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $unit_type = 'all', $event_type = 'all') {
    $form['bulk_update'] = [
      '#type' => 'fieldset',
      '#title' => t('Update event state'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    $form['bulk_update']['event_type'] = [
      '#type' => 'hidden',
      '#value' => $event_type,
    ];

    if ($unit_type == 'all') {
      $types = bat_unit_get_types();

      $types_options = [];

      foreach ($types as $type) {
        $type_bundle = bat_type_bundle_load($type->bundle());

        if (is_array($type_bundle->default_event_value_field_ids)) {
          if (isset($type_bundle->default_event_value_field_ids[$event_type]) && !empty($type_bundle->default_event_value_field_ids[$event_type])) {
            $types_options[$type->id()] = $type->label();
          }
        }
      }

      $form['bulk_update']['type'] = [
        '#type' => 'select',
        '#title' => t('Type'),
        '#options' => $types_options,
        '#required' => TRUE,
      ];
    }
    else {
      $form['bulk_update']['type'] = [
        '#type' => 'hidden',
        '#value' => $unit_type,
      ];
    }

    $form['bulk_update'] += bat_date_range_fields();

    $form['bulk_update']['state'] = [
      '#type' => 'select',
      '#title' => t('State'),
      '#options' => bat_unit_state_options($event_type, ['blocking' => 0]),
      '#required' => TRUE,
    ];

    $form['bulk_update']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Update'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $start_date = new \DateTime($values['bat_start_date']);
    $end_date = new \DateTime($values['bat_end_date']);
    $end_date->sub(new \DateInterval('PT1M'));

    $event_type = bat_event_type_load($values['event_type']);
    $event_state = $values['state'];
    $type = bat_type_load($values['type']);

    $units = bat_unit_load_multiple(NULL, ['unit_type_id' => $type->id()]);

    foreach ($units as $unit) {
      $event = bat_event_create([
        'type' => $event_type->id(),
        'uid' => $type->uid->entity->uid->value,
      ]);

      $event_dates = [
        'value' => $start_date->format('Y-m-d'),
        'end_value' => $end_date->format('Y-m-d'),
      ];
      $event->set('event_dates', $event_dates);

      $target_field_name = 'event_' . $event_type->getTargetEntityType() . '_reference';
      $event->set($target_field_name, $unit->id());

      $event->set('event_state_reference', $event_state);

      $event->save();
    }
  }

}
