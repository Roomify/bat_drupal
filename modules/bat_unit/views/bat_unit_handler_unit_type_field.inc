<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying the correct label
 * for unit types.
 */


class bat_unit_handler_unit_type_field extends views_handler_field {
  function construct() {
    parent::construct();
  }

  function render($values) {
    $unit_type = bat_unit_type_load($values->bat_units_type);
    return $unit_type->label;
  }

}
