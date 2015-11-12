<?php

/**
 * @file
 * Contains \Drupal\bat\AvailabilityEventInterface.
 */

namespace Drupal\bat;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\bat\EntityUnitInterface;
use Drupal\bat\EntityAvailabilityStateInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Availability Event entities.
 *
 * @ingroup bat
 */
interface AvailabilityEventInterface extends ContentEntityInterface, EntityChangedInterface, EntityUnitInterface, EntityAvailabilityStateInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

}
