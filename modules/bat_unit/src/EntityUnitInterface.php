<?php

/**
 * @file
 * Contains \Drupal\bat_unit\EntityUnitInterface.
 */

namespace Drupal\bat_unit;

/**
 * Defines a common interface for entities that have an associated bat Unit.
 */
interface EntityUnitInterface {

  /**
   * Returns the entity's Unit entity.
   *
   * @return \Drupal\bat_unit\UnitInterface
   *   The Unit entity.
   */
  public function getUnit();

  /**
   * Sets the entity's Unit entity.
   *
   * @param \Drupal\bat_unit\UnitInterface $unit
   *   The Unit entity.
   *
   * @return $this
   */
  public function setUnit(UnitInterface $unit);

  /**
   * Returns the entity's Unit ID.
   *
   * @return int|null
   *   The Unit bat ID, or NULL in case the Unit ID field has not been set on
   *   the entity.
   */
  public function getUnitId();

  /**
   * Sets the entity's Unit ID.
   *
   * @param int $unit_id
   *   The Unit entity id.
   *
   * @return $this
   */
  public function setUnitId($unit_id);

}
