<?php

/**
 * @file
 * Contains \Drupal\bat_unit\UnitTypeInterface.
 */

namespace Drupal\bat_unit;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Unit type entities.
 *
 * @ingroup bat
 */
interface UnitTypeInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

}
