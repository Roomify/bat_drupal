<?php

/**
 * @file
 * Class CalendarEventFormatter
 */

namespace Drupal\bat_event_ui;

use Roomify\Bat\Event\Event;
use Roomify\Bat\Event\EventInterface;
use Roomify\Bat\EventFormatter\AbstractEventFormatter;

class FullCalendarEventFormatter extends AbstractEventFormatter {

  /**
   * @var string
   */
  private $event_type;

  /**
   * @param $event_type
   */
  public function __construct($event_type) {
    $this->event_type = $event_type;
  }

  /**
   * {@inheritdoc}
   */
  public function format(EventInterface $event) {
    // Load the unit entity from Drupal
    $bat_unit = bat_unit_load($event->getUnitId());

    // Get the unit entity default value
    $default_value = $bat_unit->getEventDefaultValue($this->event_type->type);

    // This is the value actually stored in the event if the event is represented
    // in the database
    $value = 0;

    // If we are dealing with fixed event states then we need to load the
    // info regarding that state so as to format our event
    if ($this->event_type->fixed_event_states) {

      // Get the default state info which will provide the default value for formatting
      $state_info = bat_event_load_state($default_value);

      // However if the event is in the database then load the actual event and get it's value
      if (!$event->getValue() == 0) {
        // Load the event from the database to get the actual state and load that info
        $bat_event = bat_event_load($event->getValue());
        $temp_value = $bat_event->getEventValue();
        $state_info = bat_event_load_state($bat_event->getEventValue());
      }


      $formatted_event = array(
        'start' => $event->startYear() . '-' . $event->startMonth('m') . '-' . $event->startDay('d') . 'T' . $event->startHour('H') . ':' . $event->startMinute() . ':00Z',
        'end' => $event->endYear() . '-' . $event->endMonth('m') . '-' . $event->endDay('d') . 'T' . $event->endHour('H') . ':' . $event->endMinute() . ':00Z',
        'title' => $state_info['calendar_label'],
        'color' => $state_info['color'],
        'blocking' => 1,
      );

        // Render non blocking events in the background.
      if ($state_info['blocking'] == 0) {
        $formatted_event['rendering'] = 'background';
        $formatted_Event['blocking'] == 0;
      }

    }
    // If this is not a fixed state event deal with it here
    else {
      if (!$event->getValue() == 0) {
        $bat_event = bat_event_load($event->getValue());
        // Change the default value to the one that the event actually stores in the entity
        $default_value = $bat_event->getEventValue();
      }
      $formatted_event = array(
        'start' => $event->startYear() . '-' . $event->startMonth('m') . '-' . $event->startDay('d') . 'T' . $event->startHour('H') . ':' . $event->startMinute() . ':00Z',
        'end' => $event->endYear() . '-' . $event->endMonth('m') . '-' . $event->endDay('d') . 'T' . $event->endHour('H') . ':' . $event->endMinute() . ':00Z',
        'title' => $bat_unit->formatEventValue($this->event_type->type, $default_value),
      );

      if ($event->getValue() < 100) {
        $formatted_event['color']  = 'orange';
      }
      elseif ($event->getValue() >= 100) {
        $formatted_event['color'] = 'green';
      }
      $formatted_event['rendering'] = 'background';
    }

    $formatted_event['type'] = $this->event_type->type;

    return $formatted_event;
  }

}
