<?php

/**
 * @file
 * Contains \Drupal\bat\Entity\TypeGroupViewsData.
 */

namespace Drupal\bat\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Type Group entities.
 */
class TypeGroupViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    return $data;
  }

}
