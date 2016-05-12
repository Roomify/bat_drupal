<?php

namespace Drupal\bat_event\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class BatEventStatesForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bat_event_states_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if ($event_type->fixed_event_states) {
      $event_states = bat_event_get_states($event_type->type);

      $header = array(t('ID'), t('Machine name'), t('Label'), t('Blocking'), t('Operations'));

      $default_state = 0;

      $rows = array();
      foreach ($event_states as $event_state) {
        $operations = array();

        $operations[] = array(
          'title' => t('Edit'),
          'href' => 'admin/bat/config/events/event/' . $event_state['id'] . '/edit',
        );

        if (!$event_state['locked']) {
          $operations[] = array(
            'title' => t('Delete'),
            'href' => 'admin/bat/config/events/event/' . $event_state['id'] . '/delete',
          );
        }

        $rows[] = array(
          $event_state['id'],
          $event_state['machine_name'],
          $event_state['label'],
          $event_state['blocking'],
          theme('links', array('links' => $operations, 'attributes' => array('class' => array('links', 'inline')))),
        );

        if ($event_state['default_state']) {
          $default_state = count($rows) - 1;
        }
      }

      if (!empty($rows)) {
        $form['states'] = array(
          '#type' => 'tableselect',
          '#header' => $header,
          '#options' => $rows,
          '#multiple' => FALSE,
          '#default_value' => $default_state,
          '#prefix' => '<div id="event-state-wrapper">',
          '#suffix' => '</div>',
        );

        $form['set_default'] = array(
          '#type' => 'submit',
          '#value' => t('Set default state'),
          '#limit_validation_errors' => array(array('states')),
          '#submit' => array(),
          '#ajax' => array(
            'callback' => 'bat_event_states_form_set_default',
            'wrapper' => 'event-state-wrapper',
          ),
        );
      }

      $form['new_state'] = array(
        '#type' => 'fieldset',
        '#title' => 'Create new state',
      );

      $form['event_type'] = array(
        '#type' => 'hidden',
        '#value' => $event_type->type,
      );

      $form['new_state']['label'] = array(
        '#type' => 'textfield',
        '#title' => t('Label'),
        '#required' => TRUE,
      );

      $form['new_state']['machine_name'] = array(
        '#type' => 'machine_name',
        '#maxlength' => 32,
        '#machine_name' => array(
          'exists' => 'bat_event_get_states',
          'source' => array('new_state', 'label'),
        ),
      );

      $form['new_state']['color'] = array(
        '#type' => 'textfield',
        '#title' => t('Color'),
        '#size' => 12,
        '#maxlength' => 7,
        '#element_validate' => array('bat_event_validate_hex_color'),
        '#dependency' => array('edit-row-options-colors-legend' => array('type')),
        '#prefix' => '<div class="bat-colorpicker-wrapper form-wrapper">',
        '#suffix' => '<div class="bat-colorpicker"></div></div>',
        '#attributes' => array('class' => array('bat-edit-colorpicker')),
        '#attached' => array(
          // Add Farbtastic color picker.
          'library' => array(
            array('system', 'farbtastic'),
          ),
          // Add javascript to trigger the colorpicker.
          'js' => array(drupal_get_path('module', 'bat_event') . '/js/bat_color.js'),
        ),
        '#required' => TRUE,
      );

      $form['new_state']['calendar_label'] = array(
        '#type' => 'textfield',
        '#title' => t('Calendar label'),
        '#size' => 10,
        '#maxlength' => 50,
        '#required' => TRUE,
      );

      $form['new_state']['blocking'] = array(
        '#type' => 'checkbox',
        '#title' => t('Blocking'),
      );

      $form['new_state']['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Add state'),
      );
    }
    else {
      $form['empty'] = array(
        '#markup' => t('This event type cannot define fixed states!'),
      );
    }

    return $form;
  }

  /**
   * Sets the default value for the event
   * @TODO - this may no longer be required
   */
  function bat_event_states_form_set_default($form, &$form_state) {
    $state = $form_state['values']['states'];

    db_update('bat_event_state')
      ->fields(array('default_state' => 0))
      ->condition('event_type', $form['event_type']['#value'])
      ->execute();

    db_update('bat_event_state')
      ->fields(array('default_state' => 1))
      ->condition('id', $form['states']['#options'][$state][0])
      ->execute();

    drupal_set_message(t('Default state setted!'));

    return $form['states'];
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
    $event_type = $form_state['values']['event_type'];

    $event_state = array(
      'label' => $form_state['values']['label'],
      'color' => $form_state['values']['color'],
      'calendar_label' => $form_state['values']['calendar_label'],
      'blocking' => $form_state['values']['blocking'],
      'machine_name' => $form_state['values']['machine_name'],
    );

    bat_event_save_state($event_state, $event_type);
  }

}
