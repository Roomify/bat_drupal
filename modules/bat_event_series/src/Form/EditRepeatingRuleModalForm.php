<?php

/**
 * @file
 * Contains \Drupal\bat_event_series\Form\EditRepeatingRuleModalForm.
 */

namespace Drupal\bat_event_series\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\bat_event_series\Entity\EventSeries;
use RRule\RfcParser;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class EditRepeatingRuleModalForm extends FormBase {

  /**
   * Event series object.
   *
   * @var \Drupal\bat_event_series\Entity\EventSeries
   */
  protected $event_series;

  /**
   * The tempstore object.
   *
   * @var \Drupal\user\SharedTempStore
   */
  protected $tempStore;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_repeating_rule_modal_form';
  }

  /**
   * Constructs a new EditRepeatingRuleModalForm object.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, FormBuilder $formBuilder) {
    $this->tempStore = $temp_store_factory->get('edit_repeating_rule');
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, EventSeries $bat_event_series = NULL) {
    $this->event_series = $bat_event_series;

    $event_series_type = bat_event_series_type_load($bat_event_series->bundle());

    $start_date = new \DateTime($bat_event_series->get('event_dates')->value);
    $end_date = new \DateTime($bat_event_series->get('event_dates')->end_value);

    $rrule = RfcParser::parseRRule($bat_event_series->getRRule());

    $event_type = $event_series_type->getEventGranularity();

    $form['errors'] = [
      '#markup' => '<div class="form-validation-errors"></div>',
    ];

    $form['start_date'] = [
      '#type' => ($event_type == 'bat_daily') ? 'date' : 'datetime',
      '#title' => t('Start date'),
      '#default_value' => ($event_type == 'bat_daily') ? $start_date->format('Y-m-d') : new DrupalDateTime($start_date->format('Y-m-d H:00')),
      '#date_increment' => 60,
      '#required' => TRUE,
    ];

    $form['end_date'] = [
      '#type' => ($event_series_type->getEventGranularity() == 'bat_daily') ? 'date' : 'datetime',
      '#title' => t('End date'),
      '#default_value' => ($event_type == 'bat_daily') ? $end_date->format('Y-m-d') : new DrupalDateTime($end_date->format('Y-m-d H:00')),
      '#date_increment' => 60,
      '#required' => TRUE,
    ];

    $form['repeat_frequency'] = [
      '#type' => 'select',
      '#title' => t('Repeat frequency'),
      '#options' => [
        'daily' => t('Daily'),
        'weekly' => t('Weekly'),
        'monthly' => t('Monthly'),
      ],
      '#default_value' => (isset($rrule['FREQ'])) ? strtolower($rrule['FREQ']) : '',
      '#required' => TRUE,
    ];

    $form['repeat_until'] = [
      '#type' => 'date',
      '#title' => t('Repeat until'),
      '#default_value' => (isset($rrule['UNTIL'])) ? $rrule['UNTIL']->format('Y-m-d') : '',
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update this event series'),
      '#attributes' => [
        'class' => ['button--primary'],
      ],
      '#ajax' => [
        'callback' => [$this, 'ajaxSubmit'],
        'url' => Url::fromRoute('entity.bat_event_series.edit_form_modal', ['bat_event_series' => $bat_event_series->id()]),
        'options' => [
          'query' => [
            FormBuilderInterface::AJAX_FORM_REQUEST => TRUE,
          ],
        ],
      ],
    ];

    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#attributes' => [
        'class' => ['button--danger', 'dialog-cancel'],
      ],
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $start_date = $values['start_date'];
    $end_date = $values['end_date'];

    $event_series_type = bat_event_series_type_load($this->event_series->bundle());

    $event_type = $event_series_type->getEventGranularity();

    if ($event_type == 'bat_daily') {
      $start_date = new \DateTime($start_date);
      $end_date = new \DateTime($end_date);
    }

    $date_start_date = $start_date->format('Y-m-d');
    $date_end_date = $end_date->format('Y-m-d');

    $dates_valid = TRUE;

    if ($event_type == 'bat_hourly') {
      // Validate the input dates.
      if (!$start_date instanceof DrupalDateTime) {
        $form_state->setErrorByName('start_date', $this->t('The start date is not valid.'));
        $dates_valid = FALSE;
      }
      if (!$end_date instanceof DrupalDateTime) {
        $form_state->setErrorByName('end_date', $this->t('The end date is not valid.'));
        $dates_valid = FALSE;
      }
    }

    if ($dates_valid) {
      if ($end_date <= $start_date) {
        $form_state->setErrorByName('end_date', $this->t('End date must be after the start date.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = [
      'start_date' => $form_state->getValue('start_date'),
      'end_date' => $form_state->getValue('end_date'),
      'repeat_frequency' => $form_state->getValue('repeat_frequency'),
      'repeat_until' => $form_state->getValue('repeat_until'),
    ];

    $this->tempStore->set($this->currentUser()->id(), $values);
  }

  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $messages = ['#type' => 'status_messages'];
    $response->addCommand(new HtmlCommand('.form-validation-errors', $messages));

    if (!$form_state->getErrors()) {
      $response->addCommand(new CloseModalDialogCommand());

      $modal_form = $this->formBuilder->getForm('Drupal\bat_event_series\Form\EditRepeatingRuleConfirmationModalForm', $this->event_series);
      $modal_form['#attached']['library'][] = 'core/drupal.dialog.ajax';

      $response->addCommand(new OpenModalDialogCommand($this->t('Edit repeating rule'), $modal_form, ['width' => 600]));
    }

    return $response;
  }

}
