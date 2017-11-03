<?php

/**
 * @file
 * Contains \Drupal\bat_booking\Entity\BookingViewsData.
 */

namespace Drupal\bat_booking\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 *
 */
class BookingViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['booking']['nights']['field'] = [
      'title' => t('Nights'),
      'help' => t('Provide number of nights.'),
      'id' => 'bat_booking_handler_night_field',
    ];

    return $data;
  }

}
