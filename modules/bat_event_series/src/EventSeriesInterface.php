<?php

/**
 * @file
 * Contains \Drupal\bat_event_series\EventSeriesInterface.
 */

namespace Drupal\bat_event_series;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Event Series entities.
 *
 * @ingroup bat
 */
interface EventSeriesInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
}
