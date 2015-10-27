<?php

/**
 * This field handler aggregates operations that can be done on a booking
 * under a single field providing a more flexible way to present them in a view
 */
class bat_booking_handler_booking_operations_field extends views_handler_field {
  function construct() {
    parent::construct();

    $this->additional_fields['booking_id'] = 'booking_id';
  }

  function query() {
    $this->ensure_my_table();
    $this->add_additional_fields();
  }

  function render($values) {
    $links = menu_contextual_links('bat_booking', 'admin/bat/bookings/booking', array($this->get_value($values, 'booking_id')));
    if (!empty($links)) {
      return theme('links', array(
        'links' => $links,
        'attributes' => array(
          'class' => array('links',
            'inline',
            'operations'
          ),
        ),
      ));
    }
  }
}
