<?php

/**
 * @file
 * Contains \Drupal\bat_unit\Entity\UnitType.
 */

namespace Drupal\bat_unit\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Unit type entities.
 */
class UnitTypeViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['unit_type']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('Unit type'),
      'help' => $this->t('The Unit type ID.'),
    ];

    $data['unit_type']['calendars'] = [
      'field' => [
        'title' => t('Event Management'),
        'help' => t('Display links to manage all calendars for this Type.'),
        'id' => 'bat_type_handler_type_calendars_field',
      ],
    ];

    return $data;
  }

}
