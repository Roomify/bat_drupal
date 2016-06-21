<?php

namespace Drupal\bat_fullcalendar\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
	  // If any info missing we cannot load the event.
	  if ($event_id == NULL || $start_date === 0 || $end_date === 0) {
	    $output[] = ctools_modal_command_dismiss();
	    drupal_set_message(t('Unable to load event.'), 'error');
	  }

	  $modal_content = \Drupal::moduleHandler()->invokeAll('bat_fullcalendar_modal_content', array($entity_id, $event_type, $event_id, $start_date, $end_date));
	  $modal_content = array_pop($modal_content);

	  $response = new AjaxResponse();
	  $response->addCommand(new OpenModalDialogCommand($modal_content['title'], $modal_content['content'], array()));
	  return $response;
	}

}
