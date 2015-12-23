<?php

/**
 * @file
 * Contains a Views field handler to take care of filter event status options.
 */

class bat_event_handler_filter_event_status extends views_handler_filter_many_to_one {
  function get_value_options() {
    $options = array(
      '0' => 'Unconfirmed',
      '1' => 'Confirmed',
    );

    $this->value_options = $options;
  } 
}
