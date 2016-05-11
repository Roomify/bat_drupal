<?php

namespace Drupal\bat_event_ui\Controller;

use Drupal\Core\Controller\ControllerBase;

class BatEventUIController extends ControllerBase {
	function calendarPage() {
		$page['p'] = array(
			'#markup' => render(bat_event_ui_calendar_page(1, 'availability')),
		);

		return $page;
	}
}
