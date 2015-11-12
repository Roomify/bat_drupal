<?php

/**
 * @file
 * Contains \Drupal\bat\EntityAvailabilityStateInterface.
 */

namespace Drupal\bat;

/**
 * Defines a common interface for entities that have an associated
 * Availability State.
 */
interface EntityAvailabilityStateInterface {

  /**
   * Returns the entity's AvailabilityState entity.
   *
   * @return \Drupal\bat\AvailabilityStateInterface
   *   The AvailabilityState entity.
   */
  public function getAvailabilityState();

  /**
   * Sets the entity's AvailabilityState entity.
   *
   * @param \Drupal\bat\AvailabilityStateInterface $unit
   *   The AvailabilityState entity.
   *
   * @return $this
   */
  public function setAvailabilityState(AvailabilityStateInterface $state);

  /**
   * Returns the entity's AvailabilityState ID.
   *
   * @return int|null
   *   The AvailabilityState bat ID, or NULL in case the AvailabilityState ID field has not been set on
   *   the entity.
   */
  public function getAvailabilityStateId();

  /**
   * Sets the entity's AvailabilityState ID.
   *
   * @param int $state_id
   *   The AvailabilityState entity id.
   *
   * @return $this
   */
  public function setAvailabilityStateId($state_id);

}
