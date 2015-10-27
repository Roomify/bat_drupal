<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying edit links
 * as fields
 */


class bat_booking_handler_edit_link_field extends bat_booking_handler_link_field {
  function construct() {
    parent::construct();
    $this->additional_fields['type'] = 'type';
  }


  function render($values) {
    $type = $values->{$this->aliases['type']};

    //Creating a dummy booking to check access against
    $dummy_booking = (object) array('type' => $type);
    if (!bat_booking_access('update', $dummy_booking)) {
      return;
    }
    $text = !empty($this->options['text']) ? $this->options['text'] : t('edit');
    $booking_id = $values->{$this->aliases['booking_id']};

    return l($text, 'admin/bat/bookings/booking/' . $booking_id . '/edit');
  }
}
