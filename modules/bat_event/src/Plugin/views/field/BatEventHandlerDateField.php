<?php

/**
 * A extension of the views date handler to allow for some data
 * transformations
 *
 * @ingroup views_field_handlers
 */

namespace Drupal\bat_event\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\Date;

/**
 * @ViewsField("bat_event_handler_date_field")
 */
class BatEventHandlerDateField extends Date {

  public function construct() {
    parent::construct();
  }

  public function render(ResultRow $values) {
    $value = $this->get_value($values);
    $date = new DateTime($value);

    if ($this->table == 'bat_events' && $this->field == 'end_date') {
      // Add a minute to then end date.
      $date->add(new DateInterval('PT1M'));
    }

    $value = $date->getTimestamp();
    $format = $this->options['date_format'];
    if (in_array($format, ['custom', 'raw time ago', 'time ago', 'raw time span', 'time span', 'raw time span', 'inverse time span', 'time span'])) {
      $custom_format = $this->options['custom_date_format'];
    }

    if ($value) {
      $time_diff = REQUEST_TIME - $value;
      switch ($format) {
        case 'raw time ago':
          return format_interval($time_diff, is_numeric($custom_format) ? $custom_format : 2);

        case 'time ago':
          return t('%time ago', ['%time' => format_interval($time_diff, is_numeric($custom_format) ? $custom_format : 2)]);

        case 'raw time hence':
          return format_interval(-$time_diff, is_numeric($custom_format) ? $custom_format : 2);

        case 'time hence':
          return t('%time hence', ['%time' => format_interval(-$time_diff, is_numeric($custom_format) ? $custom_format : 2)]);

        case 'raw time span':
          return ($time_diff < 0 ? '-' : '') . format_interval(abs($time_diff), is_numeric($custom_format) ? $custom_format : 2);

        case 'inverse time span':
          return ($time_diff > 0 ? '-' : '') . format_interval(abs($time_diff), is_numeric($custom_format) ? $custom_format : 2);

        case 'time span':
          return t(($time_diff < 0 ? '%time hence' : '%time ago'), ['%time' => format_interval(abs($time_diff), is_numeric($custom_format) ? $custom_format : 2)]);

        case 'custom':
          if ($custom_format == 'r') {
            return format_date($value, $format, $custom_format, NULL, 'en');
          }
          return format_date($value, $format, $custom_format);

        default:
          return format_date($value, $format);
      }
    }
  }

}
