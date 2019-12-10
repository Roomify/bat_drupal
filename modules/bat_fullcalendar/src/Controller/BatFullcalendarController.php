<?php

/**
 * @file
 * Contains \Drupal\bat_fullcalendar\Controller\BatFullcalendarController.
 */

namespace Drupal\bat_fullcalendar\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\AjaxResponse;

/**
 * Returns responses for FullCalendar routes.
 */
class BatFullcalendarController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The EventManager page shows when clicking on an event in the
   * calendar - will allow a user to manipulate that event.
   *
   * @param $entity_id
   * @param $event_type
   * @param $event_id
   * @param $start_date
   * @param $end_date
   */
  public function fullcalendarEventManagement($entity_id, $event_type, $event_id, $start_date, $end_date) {
    $modal_content = $this->moduleHandler()->invokeAll('bat_fullcalendar_modal_content', [$entity_id, $event_type, $event_id, $start_date, $end_date]);
    $modal_content = array_pop($modal_content);

    $response = new AjaxResponse();

    if (isset($modal_content['commands'])) {
      foreach ($modal_content['commands'] as $command) {
        $response->addCommand($command);
      }
    }
    else {
      $response->addCommand(new OpenModalDialogCommand($modal_content['title'], $modal_content['content'], []));
    }

    return $response;
  }

}
