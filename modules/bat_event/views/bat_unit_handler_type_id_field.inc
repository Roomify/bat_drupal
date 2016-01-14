<?php

/**
 * @file
 */

class bat_unit_handler_type_id_field extends views_handler_field {
  function construct() {
    parent::construct();
  }

  function render($values) {
    if (isset($values->field_event_unit_reference[0]['raw']['entity']->type_id)) {

    	$type = bat_type_load($values->field_event_unit_reference[0]['raw']['entity']->type_id);

    	return $type->name;
    }
  }
}
