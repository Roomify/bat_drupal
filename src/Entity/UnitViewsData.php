<?php

/**
 * @file
 * Contains \Drupal\bat\Entity\Unit.
 */

namespace Drupal\bat\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Unit entities.
 */
class UnitViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['unit']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Unit'),
      'help' => $this->t('The Unit ID.'),
    );

    return $data;
  }

}
