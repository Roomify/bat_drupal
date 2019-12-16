<?php

/**
 * @file
 * Contains \Drupal\bat_unit\EntityUnitTypeInterface.
 */

namespace Drupal\bat_unit;

/**
 * Defines a common interface for entities that have a UnitType.
 *
 * A UnitType is an entity that groups Unit entities.
 */
interface EntityUnitTypeInterface {

  /**
   * Returns the entity's UnitType entity.
   *
   * @return \Drupal\bat_unit\UnitTypeInterface
   *   The UnitType entity.
   */
  public function getUnitType();

  /**
   * Sets the entity's UnitType entity.
   *
   * @param \Drupal\bat_unit\UnitTypeInterface $property
   *   The UnitType entity.
   *
   * @return $this
   */
  public function setUnitType(UnitTypeInterface $property);

  /**
   * Returns the entity's UnitType ID.
   *
   * @return int|null
   *   The UnitType bat ID, or NULL in case the UnitType ID field has not been set on
   *   the entity.
   */
  public function getUnitTypeId();

  /**
   * Sets the entity's UnitType ID.
   *
   * @param int $utid
   *   The UnitType entity id.
   *
   * @return $this
   */
  public function setUnitTypeId($utid);

}
