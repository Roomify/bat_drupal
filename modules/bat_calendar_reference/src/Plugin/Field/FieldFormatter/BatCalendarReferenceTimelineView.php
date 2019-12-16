<?php

/**
 * @file
 * Contains \Drupal\bat_calendar_reference\Plugin\Field\FieldFormatter\BatCalendarReferenceTimelineView.
 */

namespace Drupal\bat_calendar_reference\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\Html;

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
    $field_type = $this->fieldDefinition->getFieldStorageDefinition()->getType();

    $calendar_id = Html::getUniqueId($this->fieldDefinition->getFieldStorageDefinition()->getName() . '-calendar-formatter');

    $header = '';

    if ($field_type == 'bat_calendar_unit_type_reference') {
      $event_type = '';
      $event_granularity = '';

      $unit_type_names = [];
      $unit_type_ids = [];

      foreach ($items as $delta => $item) {
        if ($unit_type = bat_type_load($item->unit_type_id)) {
          $unit_type_names[] = $unit_type->label();
          $unit_type_ids[] = $unit_type->id();
        }

        if ($type = bat_event_type_load($item->event_type_id)) {
          $event_type = $type->id();

          $event_granularity = $type->getEventGranularity();
        }
      }

      if (!empty($unit_type_ids)) {
        $header = '<div class="calendar-title"><h2>' . implode(', ', $unit_type_names) . '</h2></div>';

        // Inject settings in javascript that we will use.
        $fc_user_settings[$calendar_id] = [
          'unitTypes' => $unit_type_ids,
          'unitIDs' => '',
          'eventType' => $event_type,
          'calendar_id' => 'fullcalendar-scheduler',
          'modal_style' => 'default',
          'eventGranularity' => $event_granularity,
          'editable' => FALSE,
          'selectable' => FALSE,
          'background' => '1',
        ];
      }
    }
    elseif ($field_type == 'bat_calendar_unit_reference') {
      $event_type = '';
      $event_granularity = '';

      $unit_names = [];
      $unit_ids = [];

      foreach ($items as $delta => $item) {
        if ($unit = bat_unit_load($item->unit_id)) {
          $unit_names[] = $unit->label();
          $unit_ids[] = $unit->id();
        }

        if ($type = bat_event_type_load($item->event_type_id)) {
          $event_type = $type->id();

          $event_granularity = $type->getEventGranularity();
        }
      }

      if (!empty($unit_ids)) {
        $header = '<div class="calendar-title"><h2>' . implode(', ', $unit_names) . '</h2></div>';

        // Inject settings in javascript that we will use.
        $fc_user_settings[$calendar_id] = [
          'unitTypes' => 'all',
          'unitIDs' => $unit_ids,
          'eventType' => $event_type,
          'calendar_id' => 'fullcalendar-scheduler',
          'modal_style' => 'default',
          'eventGranularity' => $event_granularity,
          'editable' => FALSE,
          'selectable' => FALSE,
          'background' => '1',
        ];
      }
    }

    if (!empty($fc_user_settings)) {
      $calendar_settings = [
        'modal_style' => 'default',
        'calendar_id' => 'fullcalendar-scheduler',
        'user_settings' => ['batCalendar' => $fc_user_settings],
      ];

      return [
        '#theme' => 'bat_fullcalendar',
        '#calendar_settings' => $calendar_settings,
        '#attached' => ['library' => ['bat_calendar_reference/bat_calendar_reference']],
        '#attributes' => [
          'id' => $calendar_id,
          'class' => [
            'cal',
            'clearfix',
          ],
        ],
        '#prefix' => $header,
      ];
    }
    else {
      return [];
    }
  }

}
