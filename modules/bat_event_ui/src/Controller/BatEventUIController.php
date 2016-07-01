<?php

/**
 * @file
 * Contains \Drupal\bat_event_ui\Controller\BatEventUIController.
 */

namespace Drupal\bat_event_ui\Controller;

use Drupal\Core\Controller\ControllerBase;

class BatEventUIController extends ControllerBase {

  function calendarPage($unit_type, $event_type) {
    $calendar_page = bat_event_ui_calendar_page($unit_type, $event_type);

    $page['calendar_page'] = array(
      '#markup' => render($calendar_page),
    );

    return $page;
  }

}
