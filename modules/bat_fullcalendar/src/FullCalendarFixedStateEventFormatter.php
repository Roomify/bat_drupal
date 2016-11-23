<?php

/**
 * @file
 * Class FullCalendarFixedStateEventFormatter.
 */

namespace Drupal\bat_fullcalendar;

use Roomify\Bat\Event\Event;
use Roomify\Bat\Event\EventInterface;
use Roomify\Bat\EventFormatter\AbstractEventFormatter;

/**
 *
 */
class FullCalendarFixedStateEventFormatter extends AbstractEventFormatter {

  /**
   * @var string
   */
  private $event_type;

  /**
   * @var bool
   */
  private $background;

  /**
   * @param string $event_type
   * @param bool $background
   */
  public function __construct($event_type, $background = TRUE) {
    $this->event_type = $event_type;
    $this->background = $background;
  }

  /**
   * {@inheritdoc}
   */
  public function format(EventInterface $event) {
    $editable = FALSE;
    $context = array();

    // Load the unit entity from Drupal.
    $bat_unit = bat_unit_load($event->getUnitId());

    // Get the unit entity default value.
    $default_value = $bat_unit->getEventDefaultValue($this->event_type->type);

    // Get the default state info which will provide the default value for formatting.
    $state_info = bat_event_load_state($default_value);

    // However if the event is in the database, then load the actual event and get its value.
    if ($event->getValue()) {
      // Load the event from the database to get the actual state and load that info.
      $bat_event = bat_event_load($event->getValue());
      $temp_value = $bat_event->getEventValue();
      $state_info = bat_event_load_state($bat_event->getEventValue());

      // Set calendar label from event.
      $state_info['calendar_label'] = $bat_event->label();

      if (bat_event_access('update', $bat_event)) {
        $editable = TRUE;
      }

      $context['bat_event'] = $bat_event;
      $context['state_info'] = $state_info;
    }

    $formatted_event = array(
      'start' => $event->startYear() . '-' . $event->startMonth('m') . '-' . $event->startDay('d') . 'T' . $event->startHour('H') . ':' . $event->startMinute() . ':00',
      'end' => $event->endYear() . '-' . $event->endMonth('m') . '-' . $event->endDay('d') . 'T' . $event->endHour('H') . ':' . $event->endMinute() . ':00',
      'title' => $state_info['calendar_label'],
      'color' => $state_info['color'],
      'blocking' => 1,
      'fixed' => 1,
      'editable' => $editable,
    );

    // Render non blocking events in the background.
    if ($state_info['blocking'] == 0) {
      if ($this->background) {
        $formatted_event['rendering'] = 'background';
      }
      $formatted_event['blocking'] = 0;
    }

    $formatted_event['type'] = $this->event_type->type;

    // Allow other modules to alter the event data.
    drupal_alter('bat_fullcalendar_formatted_event', $formatted_event, $context);

    return $formatted_event;
  }

}
