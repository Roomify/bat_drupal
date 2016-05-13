<?php

namespace Drupal\bat_calendar_reference\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * @FieldFormatter(
 *   id = "bat_calendar_reference_timeline_view",
 *   label = @Translation("Timeline View"),
 *   field_types = {
 *     "bat_calendar_unit_reference",
 *     "bat_calendar_unit_type_reference",
 *   }
 * )
 */
class BatCalendarReferenceTimelineView extends FormatterBase {

	/**
	 * {@inheritdoc}
	 */
	public function viewElements(FieldItemListInterface $items, $langcode) {
	}

}
