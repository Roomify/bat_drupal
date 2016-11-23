<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying the correct label
 * for unit bundles.
 */

/**
 *
 */
class bat_unit_handler_unit_bundle_field extends views_handler_field {

  /**
   * {@inheritdoc}
   */
  public function construct() {
    parent::construct();
  }

  /**
   * {@inheritdoc}
   */
  public function render($values) {
    $unit_bundle = bat_unit_bundle_load($this->get_value($values));
    return $unit_bundle->label;
  }

}
