<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying the correct label
 * for unit bundles.
 */

namespace Drupal\bat_unit\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * @ViewsField("bat_unit_handler_unit_bundle_field")
 */
class BatUnitHandlerUnitBundleField extends FieldPluginBase {

  function construct() {
    parent::construct();
  }

  function render($values) {
    $unit_bundle = bat_unit_bundle_load($this->get_value($values));
    return $unit_bundle->label;
  }

}
