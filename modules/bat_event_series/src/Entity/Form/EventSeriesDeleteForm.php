<?php

/**
 * @file
 * Contains \Drupal\bat_event_series\Entity\Form\EventSeriesDeleteForm.
 */

namespace Drupal\bat_event_series\Entity\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting Event entities.
 *
 * @ingroup bat
 */
class EventSeriesDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete entity %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This will delete the series and all remaining events.');
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
    $events = $query->execute();

    if (!empty($events)) {
      $date_format = \Drupal::config('bat.settings')->get('bat_date_format') ?: 'Y-m-d H:i';

      $form['delete_events'] = [
        '#type' => 'details',
        '#title' => $this->t('Delete events'),
        '#description' => $this->t('The listed events will be deleted.'),
        '#open' => TRUE,
      ];

      $form['delete_events']['events'] = [
        '#theme' => 'item_list',
        '#title' => $this->t('Events'),
        '#items' => [],
      ];

      foreach (bat_event_load_multiple($events) as $event) {
        $form['delete_events']['events']['#items'][$event->id()] = t('from @start to @end', [
          '@start' => $event->getStartDate()->format($date_format),
          '@end' => $event->getEndDate()->format($date_format),
        ]);
      }

      $form['events'] = [
        '#type' => 'hidden',
        '#value' => $events,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();

    if ($events = $form_state->getValue('events')) {
      bat_event_delete_multiple($events);
    }

    \Drupal::messenger()->addMessage($this->t('The event series has been deleted'));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
