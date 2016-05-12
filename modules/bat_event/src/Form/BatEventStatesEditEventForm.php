<?php

namespace Drupal\bat_event\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class BatEventStatesEditEventForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bat_event_states_edit_event_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $state = db_select('bat_event_state', 'n')
                      ->fields('n', array())
                      ->condition('id', $event_id)
                      ->execute()
                      ->fetchAssoc();

    $form['state_id'] = array(
      '#type' => 'hidden',
      '#value' => $event_id,
    );

    $form['event_type'] = array(
      '#type' => 'hidden',
      '#value' => $state['event_type'],
    );

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => t('State Label'),
      '#default_value' => $state['label'],
      '#required' => TRUE,
    );

    $form['color'] = array(
      '#type' => 'textfield',
      '#title' => t('Color'),
      '#size' => 12,
      '#maxlength' => 7,
      '#default_value' => $state['color'],
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
      '#default_value' => $state['calendar_label'],
      '#required' => TRUE,
    );

    $form['new_state']['blocking'] = array(
      '#type' => 'checkbox',
      '#title' => t('Blocking'),
      '#default_value' => $state['blocking'],
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Update State'),
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
    db_update('bat_event_state')
      ->fields(array(
        'label' => $form_state['values']['label'],
        'color' => $form_state['values']['color'],
        'calendar_label' => $form_state['values']['calendar_label'],
        'blocking' => $form_state['values']['blocking'],
      ))
      ->condition('id', $form_state['values']['state_id'])
      ->execute();

    $form_state['redirect'] = 'admin/bat/events/event-types/manage/' . $form_state['values']['event_type'] . '/states';
  }

}
