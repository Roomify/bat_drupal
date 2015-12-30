<?php

/**
 * @file
 * Class AbstractUnit
 */

namespace Drupal\bat;

use Drupal\bat\UnitInterface;

abstract class AbstractUnit implements UnitInterface {

  /**
   *
   */
  protected $unit_id;

  /**
   *
   */
  protected $default_value;

  /**
   *
   */
  protected $constraints;

  /**
   * {@inheritdoc}
   */
  public function getUnitId() {
    return $this->unit_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setUnitId($unit_id) {
    $this->unit_id = $unit_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue() {
    return $this->default_value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue($default_value) {
    $this->default_value = $default_value;
  }

  /**
   * {@inheritdoc}
   */
  public function setConstraints($constraints) {
    $this->constraints = $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    return $this->constraints;
  }

}
