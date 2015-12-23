<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying event status options.
 */

class bat_event_handler_field_event_status extends views_handler_field {
  function render($values) {
    if ($values->event_status == 0) return 'Unconfirmed';
    elseif ($values->event_status == 1) return 'Confirmed';

    return '';
  }
}
