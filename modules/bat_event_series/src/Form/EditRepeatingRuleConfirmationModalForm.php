<?php

/**
 * @file
 * Contains \Drupal\bat_event_series\Form\EditRepeatingRuleConfirmationModalForm.
 */

namespace Drupal\bat_event_series\Form;

use Roomify\Bat\Calendar\Calendar;
use Roomify\Bat\Store\DrupalDBStore;
use Roomify\Bat\Unit\Unit;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\bat_event_series\Entity\EventSeries;
use RRule\RRule;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class EditRepeatingRuleConfirmationModalForm extends FormBase {

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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new EditRepeatingRuleModalForm object.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->tempStore = $temp_store_factory->get('edit_repeating_rule');
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_repeating_rule_confirmation_modal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, EventSeries $bat_event_series = NULL) {
    $this->event_series = $bat_event_series;

    $values = $this->tempStore->get($this->currentUser()->id());

    $events = $this->getEvents(new \DateTime($values['start_date']), new \DateTime($values['end_date']), $values['repeat_frequency'], $values['repeat_until']);

    if (!empty($events['delete_events'])) {
      $form['delete_events'] = [
        '#type' => 'details',
        '#title' => $this->t('Events to delete'),
        '#description' => $this->t('The following events will be deleted.'),
        '#open' => TRUE,
      ];

      $form['delete_events'][] = [
        '#theme' => 'item_list',
        '#items' => $events['delete_events'],
      ];
    }

    if (!empty($events['add_events'])) {
      $form['add_events'] = [
        '#type' => 'details',
        '#title' => $this->t('Events to add'),
        '#description' => t('The following events will be added.'),
        '#open' => TRUE,
      ];

      $form['add_events'][] = [
        '#theme' => 'item_list',
        '#items' => $events['add_events'],
      ];
    }

    if (!empty($events['not_available_events'])) {
      $form['not_available_events'] = [
        '#type' => 'details',
        '#title' => $this->t('Events not available'),
        '#description' => t('The following events are not available and will not be added.'),
        '#open' => TRUE,
      ];

      $form['not_available_events'][] = [
        '#theme' => 'item_list',
        '#items' => $events['not_available_events'],
      ];
    }

    $form['events'] = [
      '#type' => 'value',
      '#value' => $events,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
      '#attributes' => [
        'class' => ['button--primary'],
      ],
      '#ajax' => [
        'callback' => [$this, 'ajaxSubmit'],
        'url' => Url::fromRoute('entity.bat_event_series.edit_confirmation_form_modal', ['bat_event_series' => $bat_event_series->id()]),
        'options' => [
          'query' => [
            FormBuilderInterface::AJAX_FORM_REQUEST => TRUE,
          ],
        ],
      ],
    ];

    if (empty($events['delete_events']) && empty($events['add_events']) && empty($events['not_available_events'])) {
      $form['no_events'] = [
        '#markup' => $this->t('No events will be created or deleted'),
      ];

      unset($form['actions']['submit']['#ajax']);
      $form['actions']['submit']['#attributes']['class'][] = 'dialog-cancel';
    }

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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $event_series_type = bat_event_series_type_load($this->event_series->bundle());
    $event_granularity = $event_series_type->getEventGranularity();

    $events = $values['events'];

    if (!empty($events['delete_events_ids'])) {
      $this->deleteEvents($events['delete_events_ids']);
    }
    if (!empty($events['add_events'])) {
      $this->addEvents($events['add_events']);
    }

    $new_values = $this->tempStore->get($this->currentUser()->id());

    $rrule = new RRule([
      'FREQ' => strtoupper($new_values['repeat_frequency']),
      'UNTIL' => $new_values['repeat_until'] . 'T235959Z',
    ]);

    if ($event_granularity == 'bat_daily') {
      $event_dates = [
        'value' => $new_values['start_date']->format('Y-m-d\T00:00:00'),
        'end_value' => $new_values['end_date']->format('Y-m-d\T00:00:00'),
      ];
    }
    else {
      $event_dates = [
        'value' => $new_values['start_date']->format('Y-m-d\TH:i:00'),
        'end_value' => $new_values['end_date']->format('Y-m-d\TH:i:00'),
      ];
    }

    $this->event_series->set('event_dates', $event_dates);
    $this->event_series->set('rrule', $rrule->rfcString());
    $this->event_series->save();
  }

  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $url = Url::fromRoute('entity.bat_event_series.edit_form', ['bat_event_series' => $this->event_series->id()]);
    $response->addCommand(new RedirectCommand($url->toString()));

    return $response;
  }

  /**
   * @param $start
   * @param $end
   * @param $repeat_frequency
   * @param $repeat_until
   * @return array
   */
  private function getEvents($start, $end, $repeat_frequency, $repeat_until) {
    $event_series_type = bat_event_series_type_load($this->event_series->bundle());
    $event_granularity = $event_series_type->getEventGranularity();
    $event_type = bat_event_type_load($event_series_type->getTargetEventType());

    $field_name = 'event_' . $event_series_type->getTargetEntityType() . '_reference';
    $unit = $this->event_series->get($field_name)->entity;

    $query = $this->entityTypeManager
      ->getStorage('bat_event')
      ->getQuery()
      ->condition('event_series.target_id', $this->event_series->id())
      ->condition('event_dates.value', date('Y-m-d\TH:i:s'), '>');
    $events = $query->execute();

    $current_event_dates = [];

    foreach ($events as $event_id) {
      $event = bat_event_load($event_id);

      $start_event_date = new \DateTime($event->get('event_dates')->value);
      $end_event_date = new \DateTime($event->get('event_dates')->end_value);

      if ($event_granularity == 'bat_daily') {
        $formatted_dates = $start_event_date->format('Y-m-d') . ' - ' . $end_event_date->format('Y-m-d');
      }
      else {
        $formatted_dates = $start_event_date->format('Y-m-d H:i') . ' - ' . $end_event_date->format('Y-m-d H:i');
      }

      $current_event_dates[$formatted_dates] = $event_id;
    }

    $rrule = new RRule([
      'FREQ' => strtoupper($repeat_frequency),
      'UNTIL' => $repeat_until . 'T235959Z',
      'DTSTART' => $start,
    ]);

    $not_available_events = [];
    $event_dates = [];

    $now = new \DateTime();
    foreach ($rrule as $occurrence) {
      if ($occurrence > $now) {
        $start_date = clone($occurrence);
        $end_date = clone($occurrence);

        if ($event_granularity == 'bat_daily') {
          $end_date->add($start->diff($end));

          $start_date->setTime(0, 0);
          $end_date->setTime(0, 0);

          if ($this->checkAvailability($start_date, $end_date, $event_type, $unit, $events)) {
            $event_dates[] = $start_date->format('Y-m-d') . ' - ' . $end_date->format('Y-m-d');
          }
          else {
            $not_available_events[] = $start_date->format('Y-m-d') . ' - ' . $end_date->format('Y-m-d');
          }
        }
        else {
          $start_date->setTime($start->format('H'), $start->format('i'));
          $end_date->setTime($start->format('H'), $start->format('i'));

          $end_date->add($start->diff($end));

          if ($this->checkAvailability($start_date, $end_date, $event_type, $unit, $events)) {
            $event_dates[] = $start_date->format('Y-m-d H:i') . ' - ' . $end_date->format('Y-m-d H:i');
          }
          else {
            $not_available_events[] = $start_date->format('Y-m-d H:i') . ' - ' . $end_date->format('Y-m-d H:i');
          }
        }
      }
    }

    $add_events = array_diff($event_dates, array_keys($current_event_dates));
    $delete_events = array_diff(array_keys($current_event_dates), $event_dates);

    $delete_events_ids = [];
    if (!empty($delete_events)) {
      $delete_events_ids = array_intersect_key($current_event_dates, array_flip($delete_events));
    }

    return [
      'add_events' => $add_events,
      'delete_events' => $delete_events,
      'delete_events_ids' => $delete_events_ids,
      'not_available_events' => $not_available_events,
    ];
  }

  /**
   * @param $start_date
   * @param $end_date
   * @param $event_type
   * @param $unit
   * @param $current_events
   * @return bool
   */
  private function checkAvailability($start_date, $end_date, $event_type, $unit, $current_events) {
    $target_field_name = 'event_' . $event_type->getTargetEntityType() . '_reference';

    $database = Database::getConnectionInfo('default');

    $prefix = (isset($database['default']['prefix']['default'])) ? $database['default']['prefix']['default'] : '';

    $event_store = new DrupalDBStore($event_type->id(), DrupalDBStore::BAT_EVENT, $prefix);

    $temp_end_date = clone($end_date);
    $temp_end_date->sub(new \DateInterval('PT1M'));

    $bat_units = [
      new Unit($unit->id(), 0),
    ];

    $calendar = new Calendar($bat_units, $event_store);

    $events = $calendar->getEvents($start_date, $temp_end_date);
    foreach ($events[$unit->id()] as $event) {
      $event_id = $event->getValue();

      if (!in_array($event_id, $current_events)) {
        if ($event = bat_event_load($event_id)) {
          $state = $event->get('event_state_reference')->entity;

          if ($state->getBlocking()) {
            return FALSE;
          }
        }
      }
    }

    return TRUE;
  }

  private function addEvents($events) {
    $event_series_type = bat_event_series_type_load($this->event_series->bundle());
    $event_granularity = $event_series_type->getEventGranularity();
    $event_type = bat_event_type_load($event_series_type->getTargetEventType());

    $field_name = 'event_' . $event_series_type->getTargetEntityType() . '_reference';
    $unit = $this->event_series->get($field_name)->entity;

    foreach ($events as $dates) {
      $event = bat_event_create([
        'type' => $event_type->id(),
      ]);

      list($start_date, $end_date) = explode(' - ', $dates);

      $start_date = new \DateTime($start_date);
      $end_date = new \DateTime($end_date);

      if ($event_granularity == 'bat_daily') {
        $start_date->setTime(0, 0);
        $end_date->setTime(0, 0);

        $event_dates = [
          'value' => $start_date->format('Y-m-d\T00:00:00'),
          'end_value' => $end_date->format('Y-m-d\T00:00:00'),
        ];
      }
      else {
        $event_dates = [
          'value' => $start_date->format('Y-m-d\TH:i:00'),
          'end_value' => $end_date->format('Y-m-d\TH:i:00'),
        ];
      }

      $event->set('event_dates', $event_dates);
      $event->set('event_state_reference', $this->event_series->get('event_state_reference')->entity->id());
      $event->set($field_name, $unit->id());
      $event->set('event_series', $this->event_series->id());
      $event->save();
    }
  }

  /**
   * @param $events
   */
  private function deleteEvents($events) {
    bat_event_delete_multiple($events);
  }

}
