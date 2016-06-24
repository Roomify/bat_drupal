<?php

/**
 * @file
 * Contains \Drupal\bat_event\Entity\Form\EventForm.
 */

namespace Drupal\bat_event\Entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;
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

    $form['langcode'] = array(
      '#title' => $this->t('Language'),
      '#type' => 'language_select',
      '#default_value' => $entity->getUntranslated()->language()->getId(),
      '#languages' => Language::STATE_ALL,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

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
        $event_store = new DrupalDBStore($this->entity->bundle(), DrupalDBStore::BAT_EVENT, $prefix);

        $end_date->sub(new \DateInterval('PT1M'));

        $bat_units = array(
          new Unit($values[$target_field_name][0]['target_id'], 0),
        );

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
