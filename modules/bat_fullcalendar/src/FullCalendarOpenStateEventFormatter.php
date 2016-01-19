<?php

/**
 * @file
 * Class FullCalendarOpenStateEventFormatter
 */

namespace Drupal\bat_fullcalendar;

use Roomify\Bat\Event\Event;
use Roomify\Bat\Event\EventInterface;
use Roomify\Bat\EventFormatter\AbstractEventFormatter;

class FullCalendarOpenStateEventFormatter extends AbstractEventFormatter {

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

    if ($event->getValue()) {
      $bat_event = bat_event_load($event->getValue());
      // Change the default value to the one that the event actually stores in the entity
      $default_value = $bat_event->getEventValue();
    }

    $formatted_event = array(
      'start' => $event->startYear() . '-' . $event->startMonth('m') . '-' . $event->startDay('d') . 'T' . $event->startHour('H') . ':' . $event->startMinute() . ':00Z',
      'end' => $event->endYear() . '-' . $event->endMonth('m') . '-' . $event->endDay('d') . 'T' . $event->endHour('H') . ':' . $event->endMinute() . ':00Z',
      'title' => $bat_unit->formatEventValue($this->event_type->type, $default_value),
      'blocking' => 0,
      'fixed' => 0,
    );

    if ($event->getValue() < 100) {
      $formatted_event['color']  = 'orange';
    }
    elseif ($event->getValue() >= 100) {
      $formatted_event['color'] = 'green';
    }

    $formatted_event['rendering'] = 'background';

    $formatted_event['type'] = $this->event_type->type;

    return $formatted_event;
  }

}
