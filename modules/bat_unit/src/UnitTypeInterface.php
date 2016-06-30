<?php

/**
 * @file
 * Contains \Drupal\bat_unit\UnitTypeInterface.
 */

namespace Drupal\bat_unit;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface for defining Unit type entities.
 *
 * @ingroup bat
 */
interface UnitTypeInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

}
