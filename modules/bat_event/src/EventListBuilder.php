<?php

/**
 * @file
 * Contains \Drupal\bat_event\EventListBuilder.
 */

namespace Drupal\bat_event;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Event entities.
 *
 * @ingroup bat
 */
class EventListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Event ID');
    $header['start_date'] = $this->t('Start Date');
    $header['end_date'] = $this->t('End Date');
    $header['type'] = $this->t('Type');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $date_format = \Drupal::config('bat.settings')->get('bat_date_format') ?: 'Y-m-d H:i';

    $row['id'] = $entity->id();
    $row['start_date'] = $entity->getStartDate()->format($date_format);
    $row['end_date'] = $entity->getEndDate()->format($date_format);
    $row['type'] = bat_event_type_load($entity->bundle())->label();
    return $row + parent::buildRow($entity);
  }

}
