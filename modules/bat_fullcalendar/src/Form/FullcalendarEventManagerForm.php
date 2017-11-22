<?php

/**
 * @file
 * Contains \Drupal\bat_fullcalendar\Form\FullcalendarEventManagerForm.
 */

namespace Drupal\bat_fullcalendar\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;

/**
 *
 */
class FullcalendarEventManagerForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bat_fullcalendar_event_manager_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity_id = 0, $event_type = 0, $event_id = 0, $start_date = 0, $end_date = 0) {
    if (!isset($form_state->getUserInput()['form_id'])) {
      $form_state->getUserInput()['form_id'] = '';
    }

    $new_event_id = $event_id;

    if ($form_state->getValue('change_event_status')) {
      $new_event_id = $form_state->getValue('change_event_status');
    }

    $form['#attributes']['class'][] = 'bat-management-form bat-event-form';

    // This entire form element will be replaced whenever 'changethis' is updated.
    $form['#prefix'] = '<div id="replace_textfield_div">';
    $form['#suffix'] = '</div>';

    $form['entity_id'] = [
      '#type' => 'hidden',
      '#value' => $entity_id,
    ];

    $form['event_type'] = [
      '#type' => 'hidden',
      '#value' => $event_type->id(),
    ];

    $form['event_id'] = [
      '#type' => 'hidden',
      '#value' => $event_id,
    ];

    $form['bat_start_date'] = [
      '#type' => 'hidden',
      '#value' => $start_date->format('Y-m-d H:i:s'),
    ];

    $form['bat_end_date'] = [
      '#type' => 'hidden',
      '#value' => $end_date->format('Y-m-d H:i:s'),
    ];

    $unit = entity_load($event_type->target_entity_type, $entity_id);

    $form['event_title'] = [
      '#prefix' => '<h2>',
      '#markup' => t('@unit_name', ['@unit_name' => $unit->label()]),
      '#suffix' => '</h2>',
    ];

    $date_format = \Drupal::config('bat.settings')->get('date_format') ?: 'Y-m-d H:i';
    $form['event_details'] = [
      '#prefix' => '<div class="event-details">',
      '#markup' => t('Date range selected: @startdate to @enddate', ['@startdate' => $start_date->format($date_format), '@enddate' => $end_date->format($date_format)]),
      '#suffix' => '</div>',
    ];

    if ($event_type->getFixedEventStates()) {
      $state_options = bat_unit_state_options($event_type->id());

      $form['change_event_status'] = [
        '#title' => t('Change the state for this event to') . ': ',
        '#type' => 'select',
        '#options' => $state_options,
        '#ajax' => [
          'callback' => '::ajaxEventStatusChange',
          'wrapper' => 'replace_textfield_div',
        ],
        '#empty_option' => t('- Select -'),
      ];
    }
    else {
      if (isset($event_type->default_event_value_field_ids) && !empty($event_type->default_event_value_field_ids)) {
        $field_name = $event_type->default_event_value_field_ids;

        $form['field_name'] = [
          '#type' => 'hidden',
          '#value' => $field_name,
        ];

        $field_definition = \Drupal::entityManager()->getFieldDefinitions('bat_event', $event_type->id())[$field_name];
        $items = new FieldItemList($field_definition, NULL, EntityAdapter::createFromEntity(bat_event_create(['type' => $event_type->id()])));

        $form_display = entity_get_form_display('bat_event', $event_type->id(), 'default');
        $widget = $form_display->getRenderer($field_name);

        $form['#parents'] = [];

        $form[$field_name] = $widget->form($items, $form, $form_state);
        $form[$field_name]['#weight'] = 1;

        $form['submit'] = [
          '#type' => 'submit',
          '#value' => t('Update value'),
          '#weight' => 2,
          '#ajax' => [
            'callback' => '::eventManagerAjaxSubmit',
            'wrapper' => 'replace_textfield_div',
          ],
        ];
      }
    }

    return $form;
  }

  /**
   * The callback for the change_event_status widget of the event manager form.
   */
  public function ajaxEventStatusChange($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $start_date = new \DateTime($values['bat_start_date']);
    $end_date = new \DateTime($values['bat_end_date']);
    $entity_id = $values['entity_id'];
    $event_id = $values['event_id'];
    $event_type = $values['event_type'];
    $state_id = $values['change_event_status'];

    $event = bat_event_create(['type' => $event_type]);
    $event->uid = \Drupal::currentUser()->id();

    $event->start = $start_date->getTimestamp();
    // Always subtract one minute from the end time. FullCalendar provides
    // start and end time with the assumption that the last minute is *excluded*
    // while BAT deals with times assuming that the last minute is included.
    $end_date->sub(new \DateInterval('PT1M'));
    $event->end = $end_date->getTimestamp();

    $event_type_entity = bat_event_type_load($event_type);
    // Construct target entity reference field name using this event type's target entity type.
    $target_field_name = 'event_' . $event_type_entity->target_entity_type . '_reference';
    $event->set($target_field_name, $entity_id);

    $event->set('event_state_reference', $state_id);

    $event->save();

    $state_options = bat_unit_state_options($event_type);
    $form['form_wrapper_bottom'] = [
      '#prefix' => '<div>',
      '#markup' => t('New Event state is <strong>@state</strong>.', ['@state' => $state_options[$state_id]]),
      '#suffix' => '</div>',
      '#weight' => 9,
    ];

    return $form;
  }

  /**
   * The callback for the change_event_status widget of the event manager form.
   */
  public function eventManagerAjaxSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $start_date = new \DateTime($values['bat_start_date']);
    $end_date = new \DateTime($values['bat_end_date']);
    $entity_id = $values['entity_id'];
    $event_id = $values['event_id'];
    $event_type = $values['event_type'];
    $field_name = $values['field_name'];

    $event = bat_event_create(['type' => $event_type]);
    $event->uid = \Drupal::currentUser()->id();

    $event->start = $start_date->getTimestamp();
    // Always subtract one minute from the end time. FullCalendar provides
    // start and end time with the assumption that the last minute is *excluded*
    // while BAT deals with times assuming that the last minute is included.
    $end_date->sub(new \DateInterval('PT1M'));
    $event->end = $end_date->getTimestamp();

    $event_type_entity = bat_event_type_load($event_type);
    // Construct target entity reference field name using this event type's target entity type.
    $target_field_name = 'event_' . $event_type_entity->target_entity_type . '_reference';
    $event->set($target_field_name, $entity_id);

    $event->set($field_name, $values[$field_name]);

    $event->save();

    $unit = entity_load($event_type_entity->target_entity_type, $entity_id);

    $elements = $event->{$field_name}->view(['label' => 'hidden']);
    $value = drupal_render($elements);

    $form['form_wrapper_bottom'] = [
      '#prefix' => '<div>',
      '#markup' => t('Value for <b>@name</b> changed to <b>@value</b>', ['@name' => $unit->label(), '@value' => trim(strip_tags($value->__toString()))]),
      '#suffix' => '</div>',
      '#weight' => 9,
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
  }

}
