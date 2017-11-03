<?php

/**
 * @file
 * Contains \Drupal\bat_facets\Form\FacetsAvailabilityForm.
 */

namespace Drupal\bat_facets\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;

/**
 *
 */
class FacetsAvailabilityForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bat_facets_availability_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $params = \Drupal::request()->query->all();
    $now = new \DateTime();

    // Year defaults to current year, although we are not filtering yet.
    $default_year = $now->format('Y');

    // Month doesn't have a default selection.
    $default_month = '';

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['container-inline'],
      ],
    ];

    if (isset($params['bat_start_date']) && !empty($params['bat_start_date'])) {
      $start_date = new \DateTime($params['bat_start_date']);
      $arrival = $start_date->format('Y-m-d');
    }
    if (isset($params['bat_end_date']) && !empty($params['bat_end_date'])) {
      $end_date = new \DateTime($params['bat_end_date']);
      $departure = $end_date->format('Y-m-d');
    }

    // Create unique ids and selectors for each picker.
    $start_date_id = Html::getUniqueId('datepicker-start-date');
    $start_date_selector = '#' . $start_date_id . ' .form-text';

    $end_date_id = Html::getUniqueId('datepicker-end-date');
    $end_date_selector = '#' . $start_date_id . ' .form-text';

    $date_format = \Drupal::config('bat.settings')->get('date_format') ?: 'Y-m-d H:i';

    $form['container']['arrival'] = [
      '#type' => 'date',
      '#description' => '',
      '#date_format' => $date_format,
      '#default_value' => isset($arrival) ? $arrival : '',
      '#required' => TRUE,
    ];

    $form['container']['departure'] = [
      '#type' => 'date',
      '#description' => '',
      '#date_format' => $date_format,
      '#default_value' => isset($departure) ? $departure : '',
      '#required' => TRUE,
    ];

    $form['container']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Search',
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
    $values = $form_state->getValues();

    $form_state->setRedirectUrl(Url::fromUserInput('?bat_start_date=' . $values['arrival'] . '&bat_end_date=' . $values['departure']));
  }

}
