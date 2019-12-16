<?php

namespace Drupal\bat_booking_example\Plugin\views\field;

use Drupal\Core\Link;
use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * @ViewsField("bat_booking_example_book_this_field")
 */
class BatBookingExampleBookThisField extends FieldPluginBase {

  public function query() {
  }

  public function render(ResultRow $values) {
    return Link::fromTextAndUrl(t('Book this'), 'booking/' . $_GET['bat_start_date'] . '/' . $_GET['bat_end_date'] . '/' . $this->getEntity($values)->id());
  }

}
