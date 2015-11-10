<?php

/**
 * @file
 * Contains \Drupal\bat\UnitTypeInterface.
 */

namespace Drupal\bat;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Unit type entities.
 *
 * @ingroup bat
 */
interface UnitTypeInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

}
