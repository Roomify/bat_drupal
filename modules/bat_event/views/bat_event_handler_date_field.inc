<?php

/**
 * @file
 * A extension of the views date handler to allow for some data transformations.
 *
 * @ingroup views_field_handlers
 */

/**
 *
 */
class bat_event_handler_date_field extends views_handler_field_date {

  /**
   * {@inheritdoc}
   */
  public function construct() {
    parent::construct();
  }

  /**
   * {@inheritdoc}
   */
  public function render($values) {
    $value = $this->get_value($values);
    $date = new DateTime($value);

    if ($this->table == 'bat_events' && $this->field == 'end_date') {
      // Add a minute to then end date.
      $date->add(new DateInterval('PT1M'));
    }

    $value = $date->getTimestamp();
    $format = $this->options['date_format'];
    $date_formats = array(
      'custom',
      'raw time ago',
      'time ago',
      'raw time span',
      'time span',
      'raw time span',
      'inverse time span',
      'time span',
    );
    if (in_array($format, $date_formats)) {
      $custom_format = $this->options['custom_date_format'];
    }

    if ($value) {
      $time_diff = REQUEST_TIME - $value;
      switch ($format) {
        case 'raw time ago':
          return format_interval($time_diff, is_numeric($custom_format) ? $custom_format : 2);

        case 'time ago':
          return t('%time ago', array('%time' => format_interval($time_diff, is_numeric($custom_format) ? $custom_format : 2)));

        case 'raw time hence':
          return format_interval(-$time_diff, is_numeric($custom_format) ? $custom_format : 2);

        case 'time hence':
          return t('%time hence', array('%time' => format_interval(-$time_diff, is_numeric($custom_format) ? $custom_format : 2)));

        case 'raw time span':
          return ($time_diff < 0 ? '-' : '') . format_interval(abs($time_diff), is_numeric($custom_format) ? $custom_format : 2);

        case 'inverse time span':
          return ($time_diff > 0 ? '-' : '') . format_interval(abs($time_diff), is_numeric($custom_format) ? $custom_format : 2);

        case 'time span':
          return t(($time_diff < 0 ? '%time hence' : '%time ago'), array('%time' => format_interval(abs($time_diff), is_numeric($custom_format) ? $custom_format : 2)));

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
