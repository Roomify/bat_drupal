<?php

/**
 * @file
 * Contains \Drupal\bat_event_ui\Form\BatEventUiEventTypeForm.
 */

namespace Drupal\bat_event_ui\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class BatEventUiEventTypeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bat_event_ui_event_type_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $unit_type = 'all', $event_type = 'all') {
    $event_types = bat_event_get_types();

    $event_types_options = [];
    foreach ($event_types as $ev_type) {
      if ($this->currentUser()->hasPermission('view calendar data for any ' . $ev_type->id() . ' event')) {
        $event_types_options[$ev_type->id()] = $ev_type->label();
      }
    }

    $form['event_types'] = [
      '#type' => 'select',
      '#title' => 'Event type',
      '#options' => $event_types_options,
      '#default_value' => $event_type,
      '#ajax' => [
        'callback' => '::eventTypeFormCallback',
        'wrapper' => 'unit-type-wrapper',
      ],
    ];

    $types = bat_unit_get_types();
    if (!empty($types)) {
      $types_options = [
        'all' => t('All'),
      ];

      foreach ($types as $type) {
        $type_bundle = bat_type_bundle_load($type->bundle());

        if (is_array($type_bundle->default_event_value_field_ids)) {
          if (isset($type_bundle->default_event_value_field_ids[$event_type]) && !empty($type_bundle->default_event_value_field_ids[$event_type])) {
            $types_options[$type->id()] = $type->label();
          }
        }
      }

      $form['unit_type'] = [
        '#type' => 'select',
        '#title' => 'Unit type',
        '#options' => $types_options,
        '#default_value' => $unit_type,
        '#prefix' => '<div id="unit-type-wrapper">',
        '#suffix' => '</div>',
      ];
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Change',
    ];

    return $form;
  }

  /**
   * Ajax callback for bat_event_ui_event_type_form form.
   */
  public function eventTypeFormCallback($form, FormStateInterface $form_state) {
    return $form['unit_type'];
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
    $type = $form_state->getValue('unit_type');
    $event_type = $form_state->getValue('event_types');

    $form_state->setRedirectUrl(Url::fromRoute('bat_event_ui.calendar', ['unit_type' => $type, 'event_type' => $event_type]));
  }

}
