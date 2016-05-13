<?php

namespace Drupal\bat_calendar_reference\Controller;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Symfony\Component\HttpFoundation\JsonResponse;

class BatCalendarReferenceController {

	function unitAutocomplete($entity_type, $bundle, $field_name) {
		$string = $_GET['q'];

		$field = FieldStorageConfig::loadByName($field_name);

	  $options = array(
	    'string' => $string,
	    'match' => 'contains',
	    'limit' => 10,
	  );
	  $references = bat_calendar_reference_units_potential_references($field, $options);

	  $matches = array();
	  foreach ($references as $id => $row) {
	    // Markup is fine in autocompletion results (might happen when rendered
	    // through Views) but we want to remove hyperlinks.
	    $suggestion = preg_replace('/<a href="([^<]*)">([^<]*)<\/a>/', '$2', $row['rendered']);
	    // Add a class wrapper for a few required CSS overrides.
	    $matches[$row['title'] . " [unit_id:$id]"] = '<div class="reference-autocomplete">' . $suggestion . '</div>';
	  }

	  return new JsonResponse($matches);
	}

	function unitTypeAutocomplete($entity_type, $bundle, $field_name) {
		$string = $_GET['q'];

		$field = FieldStorageConfig::loadByName($field_name);

	  $options = array(
	    'string' => $string,
	    'match' => 'contains',
	    'limit' => 10,
	  );
	  $references = bat_calendar_reference_unit_types_potential_references($field, $options);

	  $matches = array();
	  foreach ($references as $id => $row) {
	    // Markup is fine in autocompletion results (might happen when rendered
	    // through Views) but we want to remove hyperlinks.
	    $suggestion = preg_replace('/<a href="([^<]*)">([^<]*)<\/a>/', '$2', $row['rendered']);
	    // Add a class wrapper for a few required CSS overrides.
	    $matches[$row['title'] . " [type_id:$id]"] = '<div class="reference-autocomplete">' . $suggestion . '</div>';
	  }

	  return new JsonResponse($matches);
	}

	function eventTypeAutocomplete($entity_type, $bundle, $field_name) {
		$string = $_GET['q'];

		$field = FieldStorageConfig::loadByName($field_name);

	  $options = array(
	    'string' => $string,
	    'match' => 'contains',
	    'limit' => 10,
	  );
	  $references = bat_calendar_reference_event_types_potential_references($field, $options);

	  $matches = array();
	  foreach ($references as $id => $row) {
	    // Markup is fine in autocompletion results (might happen when rendered
	    // through Views) but we want to remove hyperlinks.
	    $suggestion = preg_replace('/<a href="([^<]*)">([^<]*)<\/a>/', '$2', $row['rendered']);
	    // Add a class wrapper for a few required CSS overrides.
	    $matches[$row['title'] . " [event_type_id:$id]"] = '<div class="reference-autocomplete">' . $suggestion . '</div>';
	  }

	  return new JsonResponse($matches);
	}

}
