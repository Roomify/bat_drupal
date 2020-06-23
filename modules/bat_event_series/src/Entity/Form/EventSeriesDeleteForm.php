<?php

/**
 * @file
 * Contains \Drupal\bat_event_series\Entity\Form\EventSeriesDeleteForm.
 */

namespace Drupal\bat_event_series\Entity\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting Event series entities.
 *
 * @ingroup bat
 */
class EventSeriesDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * Constructs a EventSeriesDeleteForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
    );
  }

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

    $query = $this->entityTypeManager
      ->getStorage('bat_event')
      ->getQuery()
      ->condition('event_series.target_id', $entity->id())
      ->condition('event_dates.value', date('Y-m-d\TH:i:s'), '>');
    $future_events = $query->execute();

    $query = $this->entityTypeManager
      ->getStorage('bat_event')
      ->getQuery()
      ->condition('event_series.target_id', $entity->id())
      ->condition('event_dates.value', date('Y-m-d\TH:i:s'), '<=');
    $past_events = $query->execute();

    $form['delete_events'] = [
      '#type' => 'details',
      '#title' => $this->t('Events'),
      '#open' => TRUE,
    ];

    $date_format = $this->configFactory()->get('bat.settings')->get('bat_date_format') ?: 'Y-m-d H:i';

    if (!empty($past_events)) {
      $form['delete_events']['past_events'] = [
        '#theme' => 'item_list',
        '#title' => $this->t('The following events will no longer be connected:'),
        '#items' => [],
      ];

      foreach (bat_event_load_multiple($past_events) as $event) {
        $form['delete_events']['past_events']['#items'][$event->id()] = t('from @start to @end', [
          '@start' => $event->getStartDate()->format($date_format),
          '@end' => $event->getEndDate()->format($date_format),
        ]);
      }
    }

    if (!empty($future_events)) {
      if (isset($form['delete_events']['past_events'])) {
        $form['delete_events']['past_events']['#suffix'] = '<br>';
      }

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

      $form['future_events'] = [
        '#type' => 'hidden',
        '#value' => $future_events,
      ];

      $form['past_events'] = [
        '#type' => 'hidden',
        '#value' => $past_events,
      ];
    }

    $form['description']['#weight'] = 1;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();

    if ($future_events = $form_state->getValue('future_events')) {
      bat_event_delete_multiple($future_events);
    }
    if ($past_events = $form_state->getValue('past_events')) {
      foreach (bat_event_load_multiple($past_events) as $event) {
        $event->set('event_series', []);
        $event->save();
      }
    }

    $this->messenger()->addMessage($this->t('The event series has been deleted'));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
