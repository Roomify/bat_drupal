<?php

/**
 * @file
 * Contains a Views filter handler to take care of displaying the correct label
 * for unit bundles.
 */

namespace Drupal\bat_unit\Plugin\views\field;

class BatUnitHandlerTypeIdFilter extends views_handler_filter_many_to_one {

  function construct() {
    parent::construct();
  }

  function get_value_options() {
    $types = bat_unit_get_types();

    $options = array();
    foreach ($types as $type) {
      $options[$type->type_id] = $type->name;
    }

    $this->value_options = $options;
  }

}
