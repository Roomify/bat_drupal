<?php

/**
 * @file
 * Contains \Drupal\bat_calendar_reference\Plugin\Field\FieldFormatter\BatCalendarRawFormatter.
 */

namespace Drupal\bat_calendar_reference\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * @FieldFormatter(
 *   id = "bat_calendar_reference_raw_formatter",
 *   label = @Translation("Raw"),
 *   field_types = {
 *     "bat_calendar_unit_reference",
 *     "bat_calendar_unit_type_reference",
 *   }
 * )
 */
class BatCalendarRawFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $field_type = $this->fieldDefinition->getFieldStorageDefinition()->getType();

    if ($field_type == 'bat_calendar_unit_type_reference') {
      foreach ($items as $delta => $item) {
        $elements[$delta] = [
          '#markup' => t('Unit type id: @unit_type_id - Event type id: @event_type_id', ['@unit_type_id' => $item->unit_type_id, '@event_type_id' => $item->event_type_id]),
        ];
      }
    }
    elseif ($field_type == 'bat_calendar_unit_reference') {
      foreach ($items as $delta => $item) {
        $elements[$delta] = [
          '#markup' => t('Unit id: @unit_id - Event type id: @event_type_id', ['@unit_id' => $item->unit_id, '@event_type_id' => $item->event_type_id]),
        ];
      }
    }

    return $elements;
  }

}
