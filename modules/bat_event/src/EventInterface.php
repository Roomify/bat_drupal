<?php

/**
 * @file
 * Contains \Drupal\bat_event\EventInterface.
 */

namespace Drupal\bat_event;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Event entities.
 *
 * @ingroup bat
 */
interface EventInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

	public function getStartDate();

	public function getEndDate();
}
