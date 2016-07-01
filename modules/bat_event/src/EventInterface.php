<?php

/**
 * @file
 * Contains \Drupal\bat_event\EventInterface.
 */

namespace Drupal\bat_event;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\bat_unit\UnitInterface;

/**
 * Provides an interface for defining Event entities.
 *
 * @ingroup bat
 */
interface EventInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  public function getStartDate();

  public function getEndDate();

  public function getUnit();

  public function getUnitId();

  public function setUnitId($unit_id);

  public function setUnit(UnitInterface $unit);

  public function setStartDate(\DateTime $date);

  public function setEndDate(\DateTime $date);

}
