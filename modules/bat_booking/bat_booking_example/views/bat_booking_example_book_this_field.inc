<?php

/**
 * @file
 */

/**
 *
 */
class bat_booking_example_book_this_field extends entity_views_handler_field_text {

  /**
   *
   */
  public function render($values) {
    if (isset($_GET['bat_start_date']) && isset($_GET['bat_end_date'])) {
      return l(t('Book this'), 'booking/' . $_GET['bat_start_date'] . '/' . $_GET['bat_end_date'] . '/' . $values->entity);
    }
    else {
      return '<em>' . t('Insert dates to book') . '</em>';
    }
  }

}
