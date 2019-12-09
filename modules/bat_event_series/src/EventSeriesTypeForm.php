<?php

/**
 * @file
 * Contains \Drupal\bat_event_series\EventSeriesTypeForm.
 */

namespace Drupal\bat_event_series;

use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for event series type forms.
 */
class EventSeriesTypeForm extends BundleEntityFormBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs the EventSeriesTypeForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $event_series_type = $this->entity;

    $form['name'] = [
      '#title' => t('Label'),
      '#type' => 'textfield',
      '#default_value' => $event_series_type->label(),
      '#description' => t('The human-readable name of this event series type.'),
      '#required' => TRUE,
      '#size' => 30,
    ];

    $form['type'] = [
      '#type' => 'machine_name',
      '#default_value' => $event_series_type->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => FALSE,
      '#machine_name' => [
        'exists' => ['Drupal\bat_event_series\Entity\EventSeriesType', 'load'],
        'source' => ['name'],
      ],
      '#description' => t('A unique machine-readable name for this event series type. It must only contain lowercase letters, numbers, and underscores.'),
    ];

    $form['event_granularity'] = [
      '#type' => 'select',
      '#title' => t('Event Granularity'),
      '#options' => ['bat_daily' => t('Daily'), 'bat_hourly' => t('Hourly')],
      '#default_value' => !empty($event_series_type->getEventGranularity()) ? $event_series_type->getEventGranularity() : 'bat_daily',
    ];

    $options = [];
    foreach (bat_event_get_types() as $id => $event_type) {
      $options[$id] = $event_type->label();
    }

    $form['target_event_type'] = [
      '#type' => 'select',
      '#title' => t('Target Event Type'),
      '#options' => $options,
      '#default_value' => $event_series_type->getTargetEventType(),
    ];

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Save event series type');
    $actions['delete']['#value'] = t('Delete event series type');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $id = trim($form_state->getValue('type'));
    // '0' is invalid, since elsewhere we check it using empty().
    if ($id == '0') {
      $form_state->setErrorByName('type', $this->t("Invalid machine-readable name. Enter a name other than %invalid.", ['%invalid' => $id]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $type = $this->entity;

    $type->set('type', trim($type->id()));
    $type->set('name', trim($type->label()));

    $status = $type->save();

    $t_args = ['%name' => $type->label()];

    if ($status == SAVED_UPDATED) {
      $this->messenger()->addMessage(t('The event series type %name has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      $this->messenger()->addMessage(t('The event series type %name has been added.', $t_args));
    }

    $form_state->setRedirectUrl($type->toUrl('collection'));
  }

}
