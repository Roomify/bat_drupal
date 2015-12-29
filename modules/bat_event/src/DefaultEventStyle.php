<?php

/**
 * @file
 * Class DefaultEventStyle
 */

use Drupal\bat\Event;
use Drupal\bat_event\EventStyle;
use Drupal\bat_event\AbstractEventStyle;

class DefaultEventStyle extends AbstractEventStyle {

  /**
   * {@inheritdoc}
   */
  public function format($event_type) {
    $ev_type = bat_event_type_load($event_type);

    $bat_unit = bat_unit_load($this->event->getUnitId());
    $default_value = $bat_unit->getDefaultValue($event_type);

    if ($ev_type->fixed_event_states) {
      $state_event = bat_event_load_state($this->event->value);

      if ($state_event === FALSE) {
        $default_state = bat_event_load_state($default_value);

        $event = array(
          'start' => $this->event->startYear() . '-' . $this->event->startMonth('m') . '-' . $this->event->startDay('d') . 'T' . $this->event->startHour('H') . ':' . $this->event->startMinute() . ':00Z',
          'end' => $this->event->endYear() . '-' . $this->event->endMonth('m') . '-' . $this->event->endDay('d') . 'T' . $this->event->endHour('H') . ':' . $this->event->endMinute() . ':00Z',
          'title' => $default_state['calendar_label'],
          'color' => $default_state['color'],
        );
      }
      else {
        $event = array(
          'start' => $this->event->startYear() . '-' . $this->event->startMonth('m') . '-' . $this->event->startDay('d') . 'T' . $this->event->startHour('H') . ':' . $this->event->startMinute() . ':00Z',
          'end' => $this->event->endYear() . '-' . $this->event->endMonth('m') . '-' . $this->event->endDay('d') . 'T' . $this->event->endHour('H') . ':' . $this->event->endMinute() . ':00Z',
          'title' => $state_event['calendar_label'],
          'color' => $state_event['color'],
        );
      }
    }
    else {
      $event = array(
        'start' => $this->event->startYear() . '-' . $this->event->startMonth('m') . '-' . $this->event->startDay('d') . 'T' . $this->event->startHour('H') . ':' . $this->event->startMinute() . ':00Z',
        'end' => $this->event->endYear() . '-' . $this->event->endMonth('m') . '-' . $this->event->endDay('d') . 'T' . $this->event->endHour('H') . ':' . $this->event->endMinute() . ':00Z',
        'title' => $this->event->value,
      );

      if ($this->event->value < 100) {
        $event['color']  = 'orange';
      }
      elseif ($this->event->value >= 100) {
        $event['color'] = 'green';
      }
    }

    return $event;
  }

}
