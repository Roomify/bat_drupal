<?php

/**
 * @file
 * This file contains no working PHP code; it exists to provide additional
 * documentation for doxygen as well as to document hooks in the standard
 * Drupal manner.
 */

/**
 * Allow other modules to alter calendar settings.
 *
 * @param $calendar_settings
 */
function hook_bat_calendar_settings_alter(&$calendar_settings) {
  // No example.
}

/**
 * Allow other modules to change the modal style.
 *
 * @param $modal_style
 */
function hook_bat_fullcalendar_modal_style_alter(&$modal_style) {
  // No example.
}

/**
 * Allow other modules to change the modal content.
 *
 * @param $unit
 * @param $event_type
 * @param $event_id
 * @param $start_date
 * @param $end_date
 */
function hook_bat_fullcalendar_modal_content($unit, $event_type, $event_id, $start_date, $end_date) {
  // No example.
}

/**
 * Allow other modules to change calendar events.
 *
 * @param $formatted_event
 */
function hook_bat_fullcalendar_formatted_event_alter(&$formatted_event) {
  // Hide booking names for non-privileged users.
  if ($formatted_event['type'] == 'availability' && !\Drupal::currentUser()->hasPermission('create bat_event entities of bundle availability')) {
    if ($formatted_event['blocking']) {
      $formatted_event['title'] = t('Not Available');
      $formatted_event['color'] = '#CC2727';
    }
  }
}
