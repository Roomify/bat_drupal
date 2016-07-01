<?php

/**
 * @file
 * Contains \Drupal\bat\TypeGroupInterface.
 */

namespace Drupal\bat;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Property entities.
 *
 * @ingroup bat
 */
interface TypeGroupInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

}
