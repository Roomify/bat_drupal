<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying the correct label
 * for event bundles.
 */

class bat_event_handler_event_type_field extends views_handler_field {

  function construct() {
    parent::construct();
  }

  function render($values) {
    $event_type = bat_event_type_load($this->get_value($values));
    return $event_type->label;
  }

}
