<?php

/**
 * @file
 * Contains \Drupal\bat\EntityTypeGroupInterface.
 */

namespace Drupal\bat;

/**
 * Defines a common interface for entities that have a Property.
 *
 * A Property is an entity that groups other entities.
 */
interface EntityTypeGroupInterface {

  /**
   * Returns the entity's Property entity.
   *
   * @return \Drupal\bat\TypeGroupInterface
   *   The Property entity.
   */
  public function getProperty();

  /**
   * Sets the entity's Property entity.
   *
   * @param \Drupal\bat\TypeGroupInterface $property
   *   The Property entity.
   *
   * @return $this
   */
  public function setProperty(TypeGroupInterface $property);

  /**
   * Returns the entity's Property ID.
   *
   * @return int|null
   *   The Property bat ID, or NULL in case the Property ID field has not been set on
   *   the entity.
   */
  public function getPropertyId();

  /**
   * Sets the entity's Property ID.
   *
   * @param int $pid
   *   The owner Property id.
   *
   * @return $this
   */
  public function setPropertyId($pid);

}
