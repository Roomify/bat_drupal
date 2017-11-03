<?php

/**
 * @file
 * Class FullCalendarOpenStateEventFormatter
 */

namespace Drupal\bat_fullcalendar;

use Roomify\Bat\Event\Event;
use Roomify\Bat\Event\EventInterface;
use Roomify\Bat\EventFormatter\AbstractEventFormatter;

/**
 *
 */
class FullCalendarOpenStateEventFormatter extends AbstractEventFormatter {

  /**
   * @var string
   */
  private $event_type;

  /**
   * @var bool
   */
  private $background;

  /**
   * @param $event_type
   */
  public function __construct($event_type, $background = TRUE) {
    $this->event_type = $event_type;
    $this->background = $background;
  }

  /**
   * {@inheritdoc}
   */
  public function format(EventInterface $event) {
    $config = \Drupal::config('bat_fullcalendar.settings');

    $editable = FALSE;

    // Load the target entity from Drupal.
    $target_entity = entity_load($this->event_type->target_entity_type, $event->getUnitId());

    // Get the target entity default value.
    $default_value = $target_entity->getEventDefaultValue($this->event_type->id());

    if ($event->getValue()) {
      $bat_event = bat_event_load($event->getValue());

      // Change the default value to the one that the event actually stores in the entity.
      $default_value = $bat_event->getEventValue();

      if (bat_event_access($bat_event, 'update', \Drupal::currentUser())) {
        $editable = TRUE;
      }
    }

    $formatted_event = [
      'start' => $event->startYear() . '-' . $event->startMonth('m') . '-' . $event->startDay('d') . 'T' . $event->startHour('H') . ':' . $event->startMinute() . ':00',
      'end' => $event->endYear() . '-' . $event->endMonth('m') . '-' . $event->endDay('d') . 'T' . $event->endHour('H') . ':' . $event->endMinute() . ':00',
      'title' => $target_entity->formatEventValue($this->event_type->id(), $default_value),
      'blocking' => 0,
      'fixed' => 0,
      'editable' => $editable,
    ];

    if ($event->getValue() == 0) {
      $formatted_event['color'] = $config->get('bat_open_state_default_zero_color');
    }
    else {
      $formatted_event['color'] = $config->get('bat_open_state_default_color');
    }

    if ($this->background) {
      $formatted_event['rendering'] = 'background';
    }

    $formatted_event['type'] = $this->event_type->id();

    // Allow other modules to alter the event data.
    \Drupal::moduleHandler()->alter('bat_fullcalendar_formatted_event', $formatted_event);

    return $formatted_event;
  }

}
