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
    $ev_type = bat_event_type_load($this->event_type);

    $bat_unit = bat_unit_load($event->getUnitId());
    $default_value = $bat_unit->getDefaultValue($this->event_type);

    if ($ev_type->fixed_event_states) {
      $state_event = bat_event_load_state($event->getValue());

      if ($state_event === FALSE) {
        $default_state = bat_event_load_state($default_value);

        $event = array(
          'start' => $event->startYear() . '-' . $event->startMonth('m') . '-' . $event->startDay('d') . 'T' . $event->startHour('H') . ':' . $event->startMinute() . ':00Z',
          'end' => $event->endYear() . '-' . $event->endMonth('m') . '-' . $event->endDay('d') . 'T' . $event->endHour('H') . ':' . $event->endMinute() . ':00Z',
          'title' => $default_state['calendar_label'],
          'color' => $default_state['color'],
        );
      }
      else {
        $event = array(
          'start' => $event->startYear() . '-' . $event->startMonth('m') . '-' . $event->startDay('d') . 'T' . $event->startHour('H') . ':' . $event->startMinute() . ':00Z',
          'end' => $event->endYear() . '-' . $event->endMonth('m') . '-' . $event->endDay('d') . 'T' . $event->endHour('H') . ':' . $event->endMinute() . ':00Z',
          'title' => $state_event['calendar_label'],
          'color' => $state_event['color'],
        );
      }
    }
    else {
      $event = array(
        'start' => $event->startYear() . '-' . $event->startMonth('m') . '-' . $event->startDay('d') . 'T' . $event->startHour('H') . ':' . $event->startMinute() . ':00Z',
        'end' => $event->endYear() . '-' . $event->endMonth('m') . '-' . $event->endDay('d') . 'T' . $event->endHour('H') . ':' . $event->endMinute() . ':00Z',
        'title' => $event->getValue(),
      );

      if ($event->getValue() < 100) {
        $event['color']  = 'orange';
      }
      elseif ($event->getValue() >= 100) {
        $event['color'] = 'green';
      }
    }

    return $event;
  }

}
