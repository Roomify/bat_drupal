<?php

/**
 * @file
 * Contains \Drupal\bat\UnitInterface.
 */

namespace Drupal\bat;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Unit entities.
 *
 * @ingroup bat
 */
interface UnitInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

}
