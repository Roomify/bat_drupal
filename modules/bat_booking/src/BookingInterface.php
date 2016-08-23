<?php

/**
 * @file
 * Contains \Drupal\bat_booking\BookingInterface.
 */

namespace Drupal\bat_booking;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Unit entities.
 *
 * @ingroup bat
 */
interface BookingInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

}
