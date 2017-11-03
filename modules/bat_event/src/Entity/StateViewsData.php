<?php

/**
 * @file
 * Contains \Drupal\bat_event\Entity\StateViewsData.
 */

namespace Drupal\bat_event\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Event entities.
 */
class StateViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['states']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('State'),
      'help' => $this->t('The State ID.'),
    ];

    return $data;
  }

}
