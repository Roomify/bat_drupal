<?php

namespace Drupal\bat_unit\Plugin\Action;

use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Assign fixed-state event to units.
 *
 * @Action(
 *   id = "unit_set_state_action",
 *   label = @Translation("Assign fixed-state event to units"),
 *   type = "unit"
 * )
 */
class SetUnitState extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $event_types_options = array();
    $event_types = bat_event_get_types();
    foreach ($event_types as $event_type) {
      if ($event_type->fixed_event_states) {
        $event_types_options[$event_type->type] = $event_type->label;
      }
    }

    $form += bat_date_range_fields();

    $form['event_type'] = array(
      '#type' => 'select',
      '#title' => t('Event type'),
      '#options' => $event_types_options,
      '#required' => TRUE,
      '#ajax' => array(
        'callback' => 'bat_event_unit_set_state_form_callback',
        'wrapper' => 'event-state-wrapper',
      ),
    );

    if (isset($form_state['values']['event_type'])) {
      $state_options = array();
      foreach (bat_event_get_states($form_state['values']['event_type']) as $state) {
        $state_options[$state['machine_name']] = $state['label'];
      }

      $form['event_state'] = array(
        '#type' => 'select',
        '#title' => t('Event state'),
        '#options' => $state_options,
        '#required' => TRUE,
        '#prefix' => '<div id="event-state-wrapper">',
        '#suffix' => '</div>',
      );
    }
    else {
      $form['event_state'] = array(
        '#prefix' => '<div id="event-state-wrapper">',
        '#suffix' => '</div>',
      );
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $type = $entity->unit_type_id->entity;
    $type_bundle = bat_type_bundle_load($type->bundle());

    $event_state = $context['form_values']['event_state'];
    $event_type = $context['form_values']['event_type'];

    $start_date = new DateTime($context['form_values']['bat_start_date']);
    $end_date = new DateTime($context['form_values']['bat_end_date']);
    $end_date->sub(new DateInterval('PT1M'));

    if (isset($type_bundle->default_event_value_field_ids[$event_type]) && !empty($type_bundle->default_event_value_field_ids[$event_type])) {
      $event = bat_event_create(array(
        'type' => $event_type,
        'start_date' => $start_date->format('Y-m-d H:i:s'),
        'end_date' => $end_date->format('Y-m-d H:i:s'),
        'uid' => $type->uid,
        'created' => REQUEST_TIME,
      ));

      $event->event_bat_unit_reference[LANGUAGE_NONE][0]['target_id'] = $unit->unit_id;

      $state = bat_event_load_state_by_machine_name($event_state);
      $event->event_state_reference[LANGUAGE_NONE][0]['state_id'] = $state['id'];

      $event->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $result = $object->access('update', $account, TRUE)
      ->andIf($object->status->access('edit', $account, TRUE));

    return $return_as_object ? $result : $result->isAllowed();
  }

}
