<?php

/**
 * @file
 * Contains \Drupal\bat_event\EventTypeListBuilder.
 */

namespace Drupal\bat_event;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a class to build a listing of event type entities.
 *
 * @see \Drupal\bat_event\Entity\EventType
 */
class EventTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['title'] = t('Name');
    $header['fixed_event_states'] = t('States');
    $header['event_granularity'] = t('Granularity');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['title'] = [
      'data' => $entity->label(),
      'class' => ['menu-label'],
    ];
    $row['fixed_event_states'] = ($entity->getFixedEventStates()) ? t('Fixed states') : t('Open states');
    $row['event_granularity'] = ($entity->getEventGranularity() == 'bat_daily') ? t('Daily') : t('Hourly');
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    // Place the edit operation after the operations added by field_ui.module
    // which have the weights 15, 20, 25.
    if (isset($operations['edit'])) {
      $operations['edit']['weight'] = 30;
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('No event types available. <a href=":link">Add event type</a>.', [
        ':link' => Url::fromRoute('entity.bat_event_type.type_add')->toString()
      ]);
    return $build;
  }

}
