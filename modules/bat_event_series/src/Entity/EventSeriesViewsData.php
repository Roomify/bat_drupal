<?php

/**
 * @file
 * Contains \Drupal\bat_event_series\Entity\EventSeriesViewsData.
 */

namespace Drupal\bat_event_series\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Event entities.
 */
class EventSeriesViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    return $data;
  }

}
