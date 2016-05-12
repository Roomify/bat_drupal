<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying the correct label
 * for unit bundles.
 */

namespace Drupal\bat_unit\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;

class BatUnitHandlerTypeIdField extends FieldPluginBase {

  function construct() {
    parent::construct();
  }

  function render($values) {
    if ($type = bat_type_load($this->get_value($values))) {
      return $type->name;
    }
  }

}
