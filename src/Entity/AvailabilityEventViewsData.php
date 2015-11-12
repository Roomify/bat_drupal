<?php

/**
 * @file
 * Contains \Drupal\bat\Entity\AvailabilityEvent.
 */

namespace Drupal\bat\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Availability Event entities.
 */
class AvailabilityEventViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['availability_event']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Availability Event'),
      'help' => $this->t('The Availability Event ID.'),
    );

    return $data;
  }

}
