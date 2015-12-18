<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying event status options.
 */

class bat_event_handler_field_event_status extends views_handler_field {
  function render($values) {
    if ($values->bat_events_event_status == 0) return 'Unconfirmed';
    elseif ($values->bat_events_event_status == 1) return 'Confirmed';

    return '';
  }
}
