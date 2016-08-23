<?php

class bat_booking_example_book_this_field extends entity_views_handler_field_text {

  public function option_definition() {
    return array();
  }

  public function render($values) {
    return l(t('Book this'), 'booking/' . $_GET['bat_start_date'] . '/' . $_GET['bat_end_date'] . '/' . $values->entity);
  }

}
