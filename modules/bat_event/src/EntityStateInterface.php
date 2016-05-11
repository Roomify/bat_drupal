<?php

/**
 * @file
 * Contains \Drupal\bat_event\EntityStateInterface.
 */

namespace Drupal\bat_event;

/**
 * Defines a common interface for entities that have an associated State.
 */
interface EntityStateInterface {

  /**
   * Returns the entity's State entity.
   *
   * @return \Drupal\bat_event\StateInterface
   *   The State entity.
   */
  public function getState();

  /**
   * Sets the entity's State entity.
   *
   * @param \Drupal\bat_event\StateInterface $unit
   *   The State entity.
   *
   * @return $this
   */
  public function setState(StateInterface $state);

  /**
   * Returns the entity's State ID.
   *
   * @return int|null
   *   The State bat ID, or NULL in case the State ID field has not been set on
   *   the entity.
   */
  public function getStateId();

  /**
   * Sets the entity's State ID.
   *
   * @param int $state_id
   *   The State entity id.
   *
   * @return $this
   */
  public function setStateId($state_id);

}
