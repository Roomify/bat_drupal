<?php

/**
 * @file
 * Contains \Drupal\bat_event\Entity\Form\EventForm.
 */

namespace Drupal\bat_event\Entity\Form;

use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Database\Database;
use Roomify\Bat\Calendar\Calendar;
use Roomify\Bat\Store\DrupalDBStore;
use Roomify\Bat\Unit\Unit;

/**
 * Form controller for Event edit forms.
 *
 * @ingroup bat
 */
class EventForm extends ContentEntityForm {

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

    $form['langcode'] = [
      '#title' => $this->t('Language'),
      '#type' => 'language_select',
      '#default_value' => $entity->getUntranslated()->language()->getId(),
      '#languages' => Language::STATE_ALL,
    ];

    $form['#theme'] = ['bat_entity_edit_form'];
    $form['#attached']['library'][] = 'bat/bat_ui';

    $form['advanced'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['entity-meta']],
      '#weight' => 99,
    ];

    $is_new = !$entity->isNew() ? format_date($entity->getChangedTime(), 'short') : t('Not saved yet');
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
        '#markup' => '<h4 class="label inline">' . t('Author') . '</h4> ' . $entity->getOwner()->getUsername(),
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

    if ($entity->isNew()) {
      $form['start']['widget'][0]['value']['#default_value'] = '';
      $form['end']['widget'][0]['value']['#default_value'] = '';
    }
    else {
      $form['end']['widget'][0]['value']['#default_value']->add(new \DateInterval('PT1M'));
    }

    unset($form['start']['widget'][0]['value']['#description']);
    unset($form['end']['widget'][0]['value']['#description']);

    if ($event_type->getEventGranularity() == 'bat_daily') {
      $form['start']['widget'][0]['value']['#date_time_element'] = 'none';
      $form['end']['widget'][0]['value']['#date_time_element'] = 'none';
    }
    else {
      $form['start']['widget'][0]['value']['#date_increment'] = 60;
      $form['end']['widget'][0]['value']['#date_increment'] = 60;
    }

    if (\Drupal::request()->query->get(MainContentViewSubscriber::WRAPPER_FORMAT) == 'drupal_ajax') {
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

    $start_date = new \DateTime($values['start'][0]['value']->format('Y-m-d H:i:s'));
    $end_date = new \DateTime($values['end'][0]['value']->format('Y-m-d H:i:s'));

    // The end date must be greater or equal than start date.
    if ($end_date < $start_date) {
      $form_state->setErrorByName('end', t('End date must be on or after the start date.'));
    }

    $event_type = bat_event_type_load($this->entity->bundle());
    $target_field_name = 'event_' . $event_type->target_entity_type . '_reference';

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

    $end_date = $event->getEndDate();
    if ($event_type->getEventGranularity() == 'bat_daily') {
      $start_date = $event->getStartDate()->setTime(0, 0);
      $event->setStartDate($start_date);

      $end_date->setTime(0, 0);
    }

    $end_date->sub(new \DateInterval('PT1M'));
    $event->setEndDate($end_date);

    $status = $event->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Event.', [
          '%label' => $event->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Event.', [
          '%label' => $event->label(),
        ]));
    }

    $form_state->setRedirect('entity.bat_event.edit_form', ['bat_event' => $event->id()]);
  }

}
