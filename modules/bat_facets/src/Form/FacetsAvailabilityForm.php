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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class FacetsAvailabilityForm extends FormBase {

  /**
   * The current Request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Constructs a FacetsAvailabilityForm object.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   */
  public function __construct(Request $request) {
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')->getCurrentRequest()
    );
  }

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
    $params = $this->request->query->all();
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

    $date_format = $this->configFactory()->get('bat.settings')->get('date_format') ?: 'Y-m-d H:i';

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
