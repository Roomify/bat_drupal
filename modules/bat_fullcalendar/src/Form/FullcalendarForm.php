<?php

namespace Drupal\bat_fullcalendar\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class FullcalendarForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bat_fullcalendar_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['bat_fullcalendar.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('bat_fullcalendar.settings');

    $form['bat_fullcalendar_scheduler'] = [
      '#type' => 'container',
      '#prefix' => '<div id="label-settings">',
      '#suffix' => '</div>',
    ];

    $form['bat_fullcalendar_scheduler']['bat_fullcalendar_scheduler_key'] = [
      '#type' => 'radios',
      '#title' => t('FullCalendar Scheduler License'),
      '#default_value' => $config->get('bat_fullcalendar_scheduler_key'),
      '#options' => [
        'commercial' => t('Commercial License'),
        'non-commercial' => t('Non-Commercial Creative Commons'),
        'gpl' => t('GPL License'),
        'none' => t('None'),
      ],
      '#description' => t('Please visit http://fullcalendar.io/scheduler/license/ to find out about the license terms for the Scheduler View of FullCalendar'),
      '#ajax' => [
        'callback' => [$this, 'fullcalendarSettingsAjax'],
        'wrapper' => 'label-settings',
      ],
    ];

    $values = $form_state->getValues();

    if ((isset($values['bat_fullcalendar_scheduler_key']) && $values['bat_fullcalendar_scheduler_key'] == 'commercial') ||
         (!isset($values['bat_fullcalendar_scheduler_key']) && $config->get('bat_fullcalendar_scheduler_key') == 'commercial')) {
      $form['bat_fullcalendar_scheduler']['bat_fullcalendar_scheduler_commercial_key'] = [
        '#type' => 'textfield',
        '#title' => t('FullCalendar Scheduler Commercial License Key'),
        '#required' => TRUE,
        '#default_value' => $config->get('bat_fullcalendar_scheduler_commercial_key'),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * Ajax callback.
   */
  public function fullcalendarSettingsAjax(array &$form, FormStateInterface $form_state) {
    return $form['bat_fullcalendar_scheduler'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('bat_fullcalendar.settings')
      ->set('bat_fullcalendar_scheduler_key', $form_state->getValue('bat_fullcalendar_scheduler_key'))
      ->set('bat_fullcalendar_scheduler_commercial_key', $form_state->getValue('bat_fullcalendar_scheduler_commercial_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
