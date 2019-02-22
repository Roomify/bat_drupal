<?php

/**
 * @file
 * Contains \Drupal\bat_event\StateInterface.
 */

namespace Drupal\bat_event;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining State entities.
 */
interface StateInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  public function getMachineName();

  public function getColor();

  public function getCalendarLabel();

  public function getBlocking();

  public function getEventType();

  public function setColor($color);

  public function setCalendarLabel($calendar_label);

  public function setBlocking($blocking);

}
