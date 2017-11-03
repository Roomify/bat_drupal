<?php

/**
 * @file
 * Contains \Drupal\bat\Form\DateFormatForm.
 */

namespace Drupal\bat\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class DateFormatForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bat_date_format_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['bat.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('bat.settings');

    $form['bat_date_format'] = [
      '#type' => 'item',
      '#title' => t('BAT PHP Date Format'),
      '#description' => t("A custom date format for events, search summary and calendar pop-ups. Define a php date format string like 'Y-m-d H:i' (see <a href=\"@link\">http://php.net/date</a> for more details).", ['@link' => 'http://php.net/date']),
    ];

    $form['bat_date_format']['bat_date_format'] = [
      '#type' => 'textfield',
      '#size' => 12,
      '#prefix' => '<div class="container-inline form-item">' . t('Date format') . ': &nbsp;',
      '#suffix' => '</div>',
      '#default_value' => $config->get('bat_date_format'),
    ];

    $form['bat_date_format']['bat_daily_date_format'] = [
      '#type' => 'textfield',
      '#size' => 12,
      '#prefix' => '<div class="container-inline form-item">' . t('Daily date format') . ': &nbsp;',
      '#suffix' => '</div>',
      '#default_value' => $config->get('bat_daily_date_format'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('bat.settings')
      ->set('bat_date_format', $form_state->getValue('bat_date_format'))
      ->set('bat_daily_date_format', $form_state->getValue('bat_daily_date_format'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
