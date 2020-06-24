<?php

/**
 * @file
 * Contains \Drupal\bat_event\Entity\Form\EventForm.
 */

namespace Drupal\bat_event\Entity\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Roomify\Bat\Calendar\Calendar;
use Roomify\Bat\Store\DrupalDBStore;
use Roomify\Bat\Unit\Unit;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Form controller for Event edit forms.
 *
 * @ingroup bat
 */
class EventForm extends ContentEntityForm {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The current Request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Constructs a EventForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, DateFormatterInterface $date_formatter, Request $request, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->dateFormatter = $date_formatter;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('date.formatter'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    $event_type = bat_event_type_load($entity->bundle());

    $form['changed'] = [
      '#type' => 'hidden',
      '#default_value' => $entity->getChangedTime(),
    ];

    $form['#theme'] = ['bat_entity_edit_form'];
    $form['#attached']['library'][] = 'bat/bat_ui';

    $form['advanced'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['entity-meta']],
      '#weight' => 99,
    ];

    $is_new = !$entity->isNew() ? $this->dateFormatter->format($entity->getChangedTime(), 'short') : t('Not saved yet');
    $form['meta'] = [
      '#attributes' => ['class' => ['entity-meta__header']],
      '#type' => 'container',
      '#group' => 'advanced',
      '#weight' => -100,
      'changed' => [
        '#type' => 'item',
        '#wrapper_attributes' => ['class' => ['entity-meta__last-saved', 'container-inline']],
        '#markup' => '<h4 class="label inline">' . t('Last saved') . '</h4> ' . $is_new,
      ],
      'author' => [
        '#type' => 'item',
        '#wrapper_attributes' => ['class' => ['author', 'container-inline']],
        '#markup' => '<h4 class="label inline">' . t('Author') . '</h4> ' . $entity->getOwner()->getDisplayName(),
      ],
    ];

    $form['author'] = [
      '#type' => 'details',
      '#title' => t('Authoring information'),
      '#group' => 'advanced',
      '#attributes' => [
        'class' => ['type-form-author'],
      ],
      '#weight' => 90,
      '#optional' => TRUE,
      '#open' => TRUE,
    ];

    if (isset($form['uid'])) {
      $form['uid']['#group'] = 'author';
    }

    if (isset($form['created'])) {
      $form['created']['#group'] = 'author';
    }

    if ($event_type->getEventGranularity() == 'bat_daily') {
      $form['event_dates']['widget'][0]['value']['#date_time_element'] = 'none';
      $form['event_dates']['widget'][0]['end_value']['#date_time_element'] = 'none';
    }
    else {
      $widget_type = EntityFormDisplay::load($entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . $form_state->getStorage()['form_display']->getMode())
        ->getComponent('event_dates')['type'];

      // Don't allow entering seconds with the default daterange widget.
      if ($widget_type == 'daterange_default') {
        $form['event_dates']['widget'][0]['value']['#date_increment'] = 60;
        $form['event_dates']['widget'][0]['end_value']['#date_increment'] = 60;
      }
    }

    $form['event_dates']['widget'][0]['value']['#date_timezone'] = 'UTC';
    $form['event_dates']['widget'][0]['end_value']['#date_timezone'] = 'UTC';

    if (isset($form['event_dates']['widget'][0]['value']['#default_value'])) {
      $form['event_dates']['widget'][0]['value']['#default_value']->setTimezone(new \DateTimeZone('UTC'));
    }
    if (isset($form['event_dates']['widget'][0]['end_value']['#default_value'])) {
      $form['event_dates']['widget'][0]['end_value']['#default_value']->setTimezone(new \DateTimeZone('UTC'));
    }

    if ($this->request->query->get(MainContentViewSubscriber::WRAPPER_FORMAT) == 'drupal_ajax') {
      $form['actions']['submit']['#attributes']['class'][] = 'use-ajax-submit';
      $form['actions']['delete']['#access'] = FALSE;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $entity = $this->entity;
    $event_type = bat_event_type_load($entity->bundle());

    $values = $form_state->getValues();

    $start_date = new \DateTime($values['event_dates'][0]['value']->format('Y-m-d H:i:s'));
    $end_date = new \DateTime($values['event_dates'][0]['end_value']->format('Y-m-d H:i:s'));

    // The end date must be greater or equal than start date.
    if ($end_date < $start_date) {
      $form_state->setErrorByName('event_dates', t('End date must be on or after the start date.'));
    }

    $event_type = bat_event_type_load($this->entity->bundle());
    $target_field_name = 'event_' . $event_type->getTargetEntityType() . '_reference';

    if ($event_type->getFixedEventStates()) {
      if ($values[$target_field_name][0]['target_id'] != '') {
        $database = Database::getConnectionInfo('default');

        $prefix = (isset($database['default']['prefix']['default'])) ? $database['default']['prefix']['default'] : '';

        $event_store = new DrupalDBStore($this->entity->bundle(), DrupalDBStore::BAT_EVENT, $prefix);

        $end_date->sub(new \DateInterval('PT1M'));

        $bat_units = [
          new Unit($values[$target_field_name][0]['target_id'], 0),
        ];

        $calendar = new Calendar($bat_units, $event_store);

        $events = $calendar->getEvents($start_date, $end_date);
        foreach ($events[$values[$target_field_name][0]['target_id']] as $event) {
          $event_id = $event->getValue();

          if ($event_id != $this->entity->id()) {
            if ($event = bat_event_load($event_id)) {
              $state = $event->get('event_state_reference')->entity;

              if ($state->getBlocking()) {
                $form_state->setErrorByName('', t('Cannot save this event as an event in a blocking state exists within the same timeframe.'));
                break;
              }
            }
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $event = $this->entity;
    $event_type = bat_event_type_load($event->bundle());

    if ($event_type->getEventGranularity() == 'bat_daily') {
      $start_date = $event->getStartDate()->setTime(0, 0);
      $event->setStartDate($start_date);

      $end_date = $event->getEndDate()->setTime(0, 0);
      $event->setEndDate($end_date);
    }

    $status = $event->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Event.', [
          '%label' => $event->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Event.', [
          '%label' => $event->label(),
        ]));
    }

    $form_state->setRedirect('entity.bat_event.edit_form', ['bat_event' => $event->id()]);
  }

}
