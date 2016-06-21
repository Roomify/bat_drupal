<?php

namespace Drupal\bat_fullcalendar\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

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
    $form = array();
    $new_event_id = $event_id;

    if ($form_state->getValue('change_event_status')) {
      $new_event_id = $form_state->getValue('change_event_status');
    }

    $form['#attributes']['class'][] = 'bat-management-form bat-event-form';

    // This entire form element will be replaced whenever 'changethis' is updated.
    $form['#prefix'] = '<div id="replace_textfield_div">';
    $form['#suffix'] = '</div>';

    $form['entity_id'] = array(
      '#type' => 'hidden',
      '#value' => $entity_id,
    );

    $form['event_type'] = array(
      '#type' => 'hidden',
      '#value' => $event_type->id(),
    );

    $form['event_id'] = array(
      '#type' => 'hidden',
      '#value' => $event_id,
    );

    $form['bat_start_date'] = array(
      '#type' => 'hidden',
      '#value' => $start_date->format('Y-m-d H:i:s'),
    );

    $form['bat_end_date'] = array(
      '#type' => 'hidden',
      '#value' => $end_date->format('Y-m-d H:i:s'),
    );

    $unit = entity_load($event_type->target_entity_type, $entity_id);

    $form['event_title'] = array(
      '#prefix' => '<h2>',
      '#markup' => t('@unit_name', array('@unit_name' => $unit->name)),
      '#suffix' => '</h2>',
    );

    $date_format = \Drupal::config('bat.settings')->get('date_format') ?: 'Y-m-d H:i';
    $form['event_details'] = array(
      '#prefix' => '<div class="event-details">',
      '#markup' => t('Date range selected: @startdate to @enddate', array('@startdate' => $start_date->format($date_format), '@enddate' => $end_date->format($date_format))),
      '#suffix' => '</div>',
    );

    if ($event_type->getFixedEventStates()) {
      $state_options = bat_unit_state_options($event_type->id());

      $form['change_event_status'] = array(
        '#title' => t('Change the state for this event to') . ': ',
        '#type' => 'select',
        '#options' => $state_options,
        '#ajax' => array(
          'callback' => '::bat_fullcalendar_ajax_event_status_change',
          'wrapper' => 'replace_textfield_div',
        ),
        '#empty_option' => t('- Select -'),
      );
    }
    else {
      if (isset($event_type->default_event_value_field_ids[$event_type->id()])) {
        $field_name = $event_type->default_event_value_field_ids[$event_type->id()];

        $form['field_name'] = array(
          '#type' => 'hidden',
          '#value' => $field_name,
        );

        $field = FieldStorageConfig::loadByName('event', $field_name);
        $instance = FieldConfig::loadByName('event', $field_name, $event_type->id());

        $element = array('#parents' => array());
        $widget = field_default_form('bat_event', NULL, $field, $instance, 'und', NULL, $element, $form_state);

        $form[$field_name] = $widget[$field_name];
        $form[$field_name]['#weight'] = 1;

        $form['submit'] = array(
          '#type' => 'submit',
          '#value' => t('Update value'),
          '#weight' => 2,
          '#ajax' => array(
            'callback' => 'bat_fullcalendar_event_manager_form_ajax_submit',
            'wrapper' => 'replace_textfield_div',
          ),
        );
      }
    }

    return $form;
  }

  /**
   * The callback for the change_event_status widget of the event manager form.
   */
  function bat_fullcalendar_ajax_event_status_change($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $start_date = new \DateTime($values['bat_start_date']);
    $end_date = new \DateTime($values['bat_end_date']);
    $entity_id = $values['entity_id'];
    $event_id = $values['event_id'];
    $event_type = $values['event_type'];
    $state_id = $values['change_event_status'];

    $event = bat_event_create2(array('type' => $event_type));
    $event->created = REQUEST_TIME;
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
    $form['form_wrapper_bottom'] = array(
      '#prefix' => '<div>',
      '#markup' => t('New Event state is <strong>@state</strong>.', array('@state' => $state_options[$state_id])),
      '#suffix' => '</div>',
      '#weight' => 9,
    );

    return $form;
  }

  /**
   * The callback for the change_event_status widget of the event manager form.
   */
  function bat_fullcalendar_event_manager_form_ajax_submit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $start_date = new \DateTime($values['bat_start_date']);
    $end_date = new \DateTime($values['bat_end_date']);
    $entity_id = $values['entity_id'];
    $event_id = $values['event_id'];
    $event_type = $values['event_type'];
    $field_name = $values['field_name'];

    $event = bat_event_create2(array('type' => $event_type));
    $event->created = REQUEST_TIME;
    $event->uid = \Drupal::currentUser()->id();

    $event->start = $start_date->getTimestamp();
    // Always subtract one minute from the end time. FullCalendar provides
    // start and end time with the assumption that the last minute is *excluded*
    // while BAT deals with times assuming that the last minute is included.
    $end_date->sub(new DateInterval('PT1M'));
    $event->end = $end_date->getTimestamp();

    $event_type_entity = bat_event_type_load($event_type);
    // Construct target entity reference field name using this event type's target entity type.
    $target_field_name = 'event_' . $event_type_entity->target_entity_type . '_reference';
    $event->set($target_field_name, $entity_id);

    $event->set($field_name, $values[$field_name]);

    $event->save();

    $unit = entity_load($event_type_entity->target_entity_type, $entity_id);

    $value = field_view_value('bat_event', $event, $field_name, $form_state['values'][$field_name]['und'][0]);

    $form['form_wrapper_bottom'] = array(
      '#prefix' => '<div>',
      '#markup' => t('Value for @name changed to @value', array('@name' => $unit->name, '@value' => $value['#markup'])),
      '#suffix' => '</div>',
      '#weight' => 9,
    );

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
