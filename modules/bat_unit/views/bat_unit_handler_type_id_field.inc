<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying the correct label
 * for unit bundles.
 */

class bat_unit_handler_type_id_field extends views_handler_field {

  function construct() {
    parent::construct();
  }

  function render($values) {
    if ($type = bat_type_load($this->get_value($values))) {
      return $type->name;
    }
  }

}
