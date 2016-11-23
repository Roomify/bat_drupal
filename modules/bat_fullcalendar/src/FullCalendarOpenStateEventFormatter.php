<?php

/**
 * @file
 * Class FullCalendarOpenStateEventFormatter.
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

    // Load the target entity from Drupal.
    $target_entity = entity_load_single($this->event_type->target_entity_type, $event->getUnitId());

    // Get the target entity default value.
    $default_value = $target_entity->getEventDefaultValue($this->event_type->type);

    if ($event->getValue()) {
      $bat_event = bat_event_load($event->getValue());

      // Change the default value to the one that the event actually stores in the entity.
      $default_value = $bat_event->getEventValue();

      if (bat_event_access('update', $bat_event)) {
        $editable = TRUE;
      }

      $context['bat_event'] = $bat_event;
    }

    $formatted_event = array(
      'start' => $event->startYear() . '-' . $event->startMonth('m') . '-' . $event->startDay('d') . 'T' . $event->startHour('H') . ':' . $event->startMinute() . ':00',
      'end' => $event->endYear() . '-' . $event->endMonth('m') . '-' . $event->endDay('d') . 'T' . $event->endHour('H') . ':' . $event->endMinute() . ':00',
      'title' => $target_entity->formatEventValue($this->event_type->type, $default_value),
      'blocking' => 0,
      'fixed' => 0,
      'editable' => $editable,
    );

    if ($event->getValue() == 0) {
      $formatted_event['color'] = variable_get('bat_open_state_default_zero_color', '#F3C776');
    }
    else {
      $formatted_event['color'] = variable_get('bat_open_state_default_color', '#9DDC9D');
    }

    if ($this->background) {
      $formatted_event['rendering'] = 'background';
    }

    $formatted_event['type'] = $this->event_type->type;

    // Allow other modules to alter the event data.
    drupal_alter('bat_fullcalendar_formatted_event', $formatted_event, $context);

    return $formatted_event;
  }

}
