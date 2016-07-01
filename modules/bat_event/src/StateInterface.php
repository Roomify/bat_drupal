<?php

/**
 * @file
 * Contains \Drupal\bat_event\StateInterface.
 */

namespace Drupal\bat_event;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface for defining State entities.
 */
interface StateInterface extends ContentEntityInterface {

  public function getMachineName();

  public function getColor();

  public function getCalendarLabel();

  public function getBlocking();

  public function getEventType();

}
