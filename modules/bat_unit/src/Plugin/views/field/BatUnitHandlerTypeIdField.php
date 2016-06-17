<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying the correct label
 * for unit bundles.
 */

namespace Drupal\bat_unit\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * @ViewsField("bat_unit_handler_type_id_field")
 */
class BatUnitHandlerTypeIdField extends FieldPluginBase {

  public function construct() {
    parent::construct();
  }

  public function render(ResultRow $values) {
    if ($type = bat_type_load($this->get_value($values))) {
      return $type->name;
    }
  }

}
