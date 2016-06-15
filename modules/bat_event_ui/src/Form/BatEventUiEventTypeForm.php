<?php

namespace Drupal\bat_event_ui\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

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
  public function buildForm(array $form, FormStateInterface $form_state) {
    if ($form_state->getValue('event_types') != '') {
      $event_type = $form_state->getValue('event_types');
    }

    $event_types = bat_event_get_types();
    foreach ($event_types as $ev_type) {
      if (\Drupal::currentUser()->hasPermission('view calendar data for any ' . $ev_type->id() . ' event')) {
        $event_types_options[$ev_type->id()] = $ev_type->label();
      }
    }

    $form['event_types'] = array(
      '#type' => 'select',
      '#title' => 'Event type',
      '#options' => $event_types_options,
      '#default_value' => $event_type,
      '#ajax' => array(
        'callback' => 'bat_event_ui_event_type_form_callback',
        'wrapper' => 'unit-type-wrapper',
      ),
    );

    $types = bat_unit_get_types();
    if (!empty($types)) {
      $types_options = array(
        'all' => t('All'),
      );

      foreach ($types as $type) {
        $type_bundle = bat_type_bundle_load($type->bundle());

        //if (is_array($type_bundle->default_event_value_field_ids)) {
        //  if (isset($type_bundle->default_event_value_field_ids[$event_type]) && !empty($type_bundle->default_event_value_field_ids[$event_type])) {
            $types_options[$type->id()] = $type->label();
        //  }
        //}
      }

      $form['unit_type'] = array(
        '#type' => 'select',
        '#title' => 'Unit type',
        '#options' => $types_options,
        '#default_value' => $unit_type,
        '#prefix' => '<div id="unit-type-wrapper">',
        '#suffix' => '</div>',
      );
    }

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Change',
    );

    return $form;
  }

  /**
   * Ajax callback for bat_event_ui_event_type_form form.
   */
  function bat_event_ui_event_type_form_callback($form, &$form_state) {
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

    $form_state->setRedirectUrl(Url::fromUri('admin/bat/calendar/' . $type . '/' . $event_type));
  }

}
