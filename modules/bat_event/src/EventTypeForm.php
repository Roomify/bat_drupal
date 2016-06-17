<?php

namespace Drupal\bat_event;

use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\language\Entity\ContentLanguageSettings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for node type forms.
 */
class EventTypeForm extends BundleEntityFormBase {

	/**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs the EventTypeForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
  	$form = parent::form($form, $form_state);

  	$event_type = $this->entity;

  	$form['name'] = array(
      '#title' => t('Label'),
      '#type' => 'textfield',
      '#default_value' => $event_type->label(),
      '#description' => t('The human-readable name of this event type.'),
      '#required' => TRUE,
      '#size' => 30,
    );

    $form['type'] = array(
      '#type' => 'machine_name',
      '#default_value' => $event_type->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => FALSE,
      '#machine_name' => array(
        'exists' => ['Drupal\bat_event\Entity\EventType', 'load'],
        'source' => array('name'),
      ),
      '#description' => t('A unique machine-readable name for this event type. It must only contain lowercase letters, numbers, and underscores.'),
    );

    if ($event_type->isNew()) {
      $form['fixed_event_states'] = array(
        '#type' => 'checkbox',
        '#title' => t('Fixed event states'),
      );
    }

    $form['event_granularity'] = array(
      '#type' => 'select',
      '#title' => t('Event Granularity'),
      '#options' => array('bat_daily' => t('Daily'), 'bat_hourly' => t('Hourly')),
      '#default_value' => isset($event_type->event_granularity) ? $event_type->event_granularity : 'bat_daily',
    );

    if ($event_type->isNew()) {
      // Check for available Target Entity types.
      $target_entity_types = \Drupal::moduleHandler()->invokeAll('bat_event_target_entity_types');
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
          $target_entity_info = \Drupal::entityManager()->getDefinition($target_entity_type);
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

    if (!$event_type->isNew() && $event_type->getFixedEventStates() == 0) {
      $fields_options = array();
      $fields = $this->entityManager->getFieldDefinitions('event', $event_type->id());
      foreach ($fields as $field) {
        if ($field instanceof FieldConfig) {
          $fields_options[$field->getName()] = $field->getName();
        }
      }

      $form['events'] = array(
        '#type' => 'fieldset',
        '#group' => 'additional_settings',
        '#title' => t('Events'),
        '#tree' => TRUE,
        '#weight' => 80,
      );

      $form['events'][$event_type->id()] = array(
        '#type' => 'select',
        '#title' => t('Select your default @event field', array('@event' => $event_type->label())),
        '#options' => $fields_options,
        '#default_value' => isset($event_type->default_event_value_field_ids) ? $event_type->default_event_value_field_ids : NULL,
        '#empty_option' => t('- Select a field -'),
      );
    }

    if (!$event_type->isNew()) {
      $fields_options = array();
      $fields = $this->entityManager->getFieldDefinitions('event', $event_type->id());
      foreach ($fields as $field) {
        if ($field instanceof FieldConfig) {
          $fields_options[$field->getName()] = $field->getName();
        }
      }

      $form['event_label'] = array(
        '#type' => 'fieldset',
        '#group' => 'additional_settings',
        '#title' => t('Label Source'),
        '#tree' => TRUE,
        '#weight' => 70,
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

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Save event type');
    $actions['delete']['#value'] = t('Delete event type');
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
      $form_state->setErrorByName('type', $this->t("Invalid machine-readable name. Enter a name other than %invalid.", array('%invalid' => $id)));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
  	$type = $this->entity;

  	$type->set('type', trim($type->id()));
    $type->set('name', trim($type->label()));

    $type->set('default_event_label_field_name', $form_state->getValues()['event_label']['default_event_label_field_name']);
    $type->set('default_event_value_field_ids', $form_state->getValues()['events'][$type->id()]);

    $status = $type->save();

    $t_args = array('%name' => $type->label());

    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('The event type %name has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message(t('The event type %name has been added.', $t_args));
    }

  	$form_state->setRedirectUrl($type->urlInfo('collection'));
  }

}
