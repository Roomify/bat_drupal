<?php

/**
 * @file
 * Contains \Drupal\bat_unit\Form\UnitSetStateAction.
 */

namespace Drupal\bat_unit\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class UnitSetStateAction extends FormBase {

  /**
   * The array of units.
   *
   * @var string[][]
   */
  protected $unitInfo = [];

  /**
   * The tempstore factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Constructs a UnitSetStateAction form object.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory) {
    $this->tempStoreFactory = $temp_store_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'unit_set_state_action_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->unitInfo = $this->tempStoreFactory->get('unit_set_state_action_form')->get($this->currentUser()->id());

    $values = $form_state->getValues();

    $event_types_options = [];
    $event_types = bat_event_get_types();
    foreach ($event_types as $event_type) {
      if ($event_type->getFixedEventStates()) {
        $event_types_options[$event_type->id()] = $event_type->label();
      }
    }

    $form += bat_date_range_fields();

    $form['event_type'] = [
      '#type' => 'select',
      '#title' => t('Event type'),
      '#options' => $event_types_options,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::eventTypeChange',
        'wrapper' => 'event-state-wrapper',
      ],
    ];

    if (isset($values['event_type'])) {
      $state_options = [];
      foreach (bat_event_get_states($values['event_type']) as $state) {
        $state_options[$state->getMachineName()] = $state->label();
      }

      $form['event_state'] = [
        '#type' => 'select',
        '#title' => t('Event state'),
        '#options' => $state_options,
        '#required' => TRUE,
        '#prefix' => '<div id="event-state-wrapper">',
        '#suffix' => '</div>',
      ];
    }
    else {
      $form['event_state'] = [
        '#prefix' => '<div id="event-state-wrapper">',
        '#suffix' => '</div>',
      ];
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Apply'),
    ];

    return $form;
  }

  /**
   * Ajax callback when change 'Event type'.
   */
  public function eventTypeChange(array $form, FormStateInterface $form_state) {
    return $form['event_state'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach (array_keys($this->unitInfo) as $unit_id) {
      $unit = bat_unit_load($unit_id);

      $values = $form_state->getValues();

      $type = $unit->unit_type_id->entity;
      $type_bundle = bat_type_bundle_load($type->bundle());

      $event_state = bat_event_load_state_by_machine_name($values['event_state'])->id();
      $event_type = $values['event_type'];

      $start_date = new \DateTime($values['bat_start_date']);
      $end_date = new \DateTime($values['bat_end_date']);
      $end_date->sub(new \DateInterval('PT1M'));

      if (isset($type_bundle->default_event_value_field_ids[$event_type]) && !empty($type_bundle->default_event_value_field_ids[$event_type])) {
        $event = bat_event_create(['type' => $event_type]);
        $event->start = $start_date->getTimestamp();
        $event->end = $end_date->getTimestamp();
        $event->uid = $type->uid->entity->uid->value;

        $event_type_entity = bat_event_type_load($event_type);
        // Construct target entity reference field name using this event type's target entity type.
        $target_field_name = 'event_' . $event_type_entity->getTargetEntityType() . '_reference';
        $event->set($target_field_name, $unit_id);

        $event->set('event_state_reference', $event_state);

        $event->save();
      }
    }
  }

}
