<?php

/**
 * @file
 * Contains \Drupal\bat_event_series\Entity\Form\EventSeriesDeleteEventsForm.
 */

namespace Drupal\bat_event_series\Entity\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting Event series entities.
 *
 * @ingroup bat
 */
class EventSeriesDeleteEventsForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete events from %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Are you sure you want to do this?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.bat_event_series.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $entity = $this->getEntity();

    $query = \Drupal::entityQuery('bat_event')
      ->condition('event_series.target_id', $entity->id())
      ->condition('event_dates.value', date('Y-m-d\TH:i:s'), '>');
    $future_events = $query->execute();

    $form['delete_events'] = [
      '#type' => 'details',
      '#title' => $this->t('Events'),
      '#open' => TRUE,
    ];

    $date_format = \Drupal::config('bat.settings')->get('bat_date_format') ?: 'Y-m-d H:i';

    if (empty($future_events)) {
      $form['delete_events']['future_events'] = [
        '#markup' => '<h3>' . $this->t('There are no upcoming events to delete!') . '</h3>',
      ];

      $form['description']['#access'] = FALSE;
      $form['actions']['submit']['#disabled'] = TRUE;
    }
    else {
      $form['delete_events']['future_events'] = [
        '#theme' => 'item_list',
        '#title' => $this->t('The following events will be deleted:'),
        '#items' => [],
      ];

      foreach (bat_event_load_multiple($future_events) as $event) {
        $form['delete_events']['future_events']['#items'][$event->id()] = t('from @start to @end', [
          '@start' => $event->getStartDate()->format($date_format),
          '@end' => $event->getEndDate()->format($date_format),
        ]);
      }

      $form['events'] = [
        '#type' => 'hidden',
        '#value' => $future_events,
      ];
    }

    $form['description']['#weight'] = 1;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($events = $form_state->getValue('events')) {
      bat_event_delete_multiple($events);
    }

    $this->messenger()->addMessage($this->t('The series events have been deleted'));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
