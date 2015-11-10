<?php

/**
 * @file
 * Contains \Drupal\bat\Entity\Property.
 */

namespace Drupal\bat\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Property entities.
 */
class PropertyViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['property']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Property'),
      'help' => $this->t('The Property ID.'),
    );

    return $data;
  }

}
