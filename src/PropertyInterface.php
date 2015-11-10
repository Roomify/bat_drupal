<?php

/**
 * @file
 * Contains \Drupal\bat\PropertyInterface.
 */

namespace Drupal\bat;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Property entities.
 *
 * @ingroup bat
 */
interface PropertyInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

}
