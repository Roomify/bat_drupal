<?php

/**
 * @file
 * Contains \Drupal\bat_unit\UnitInterface.
 */

namespace Drupal\bat_unit;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Unit entities.
 *
 * @ingroup bat
 */
interface UnitInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface, EntityUnitTypeInterface {

  public function getUnitType();

  public function getUnitTypeId();

  public function setUnitTypeId($utid);

  public function setUnitType(UnitTypeInterface $unit_type);

}
