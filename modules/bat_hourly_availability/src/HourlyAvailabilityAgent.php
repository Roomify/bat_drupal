<?php

/**
 * @file
 * Class HourlyAvailabilityAgent.
 */

namespace Drupal\bat_hourly_availability;

/**
 * HourlyAvailabilityAgent
 */
class HourlyAvailabilityAgent {

  /**
   * The bookable unit.
   *
   * @var BatUnit
   */
  public $unit;

  /**
   * @var DateTime
   */
  public $date;

  /**
   * @var HourlyAvailabilityEvent
   */
  public $opening_time_event = NULL;

  /**
   * Construct the HourlyAvailabilityAgent instance.
   *
   * @param BatUnit $unit
   *   The bookable unit.
   * @param DateTime $date
   */
  public function __construct($unit, $date) {
    $this->unit = $unit;
    $this->date = $date;

    $this->setWorkingHoursEvent();
  }

  /**
   *
   */
  private function setWorkingHoursEvent() {
    $opening_time = bat_hourly_availability_get_opening_time($this->unit);

    if (!empty($opening_time)) {
      if (in_array($this->date->format('w'), $opening_time['dow'])) {
        $this->opening_time_event = new HourlyAvailabilityEvent(
          new \DateTime($this->date->format('Y-m-d') . ' ' . $opening_time['opening']),
          new \DateTime($this->date->format('Y-m-d') . ' ' . $opening_time['closing'])
        );
      }
    }
    else {
      $this->opening_time_event = new HourlyAvailabilityEvent(
        new \DateTime($this->date->format('Y-m-d') . ' 00:00:00'),
        new \DateTime($this->date->format('Y-m-d') . ' 24:00:00')
      );
    }
  }

  /**
   * @return array
   */
  public function getEvents($valid_states = array(), $negate = FALSE) {
    $events = array();

    $end_date = clone($this->date);
    $end_date->add(new \DateInterval('P1D'));

    $start = $this->date->format('Y-m-d');
    $end = $end_date->format('Y-m-d');

    $query = db_select('bat_hourly_availability', 'n')
              ->fields('n', array('id', 'start_date', 'end_date', 'state'))
              ->condition('unit_id', $this->unit->unit_id)
              ->orderBy('start_date')
              ->where("start_date > '$start' and end_date < '$end'");
    $results = $query->execute()->fetchAll();

    foreach ($results as $result) {
      if ((!in_array($result->state, $valid_states) && $negate) || (in_array($result->state, $valid_states) && !$negate)) {
        $events[] = new HourlyAvailabilityEvent(
          new \DateTime($result->start_date),
          new \DateTime($result->end_date)
        );
      }
    }

    return $events;
  }

  /**
   * @param array
   *
   * @return array
   */
  public function getUndeterminedEvents($events) {
    $undetermined_events = array();

    if ($this->opening_time_event !== NULL) {
      $dates[] = $this->opening_time_event->start_date;

      foreach ($events as $event) {
        $dates[] = $event->start_date;
        $dates[] = $event->end_date;
      }

      $dates[] = $this->opening_time_event->end_date;

      for ($i = 0; $i < count($dates); $i = $i + 2) {
        if ($dates[$i] < $dates[$i + 1]) {
          $undetermined_events[] = new HourlyAvailabilityEvent($dates[$i], $dates[$i + 1]);
        }
      }
    }

    return $undetermined_events;
  }

  /**
   * @param array
   * @param string
   *
   * @return array
   */
  public function getStartingTimes($events, $duration) {
    $options = array();

    foreach ($events as $event) {
      $start_event = clone($event->start_date);
      $original_start_event = clone($start_event);

      while($start_event->modify('+ ' . $duration) <= $event->end_date) {
        $options[$original_start_event->format('H:i')] = $original_start_event->format('H:i');
        $original_start_event = clone($start_event);
      }
    }

    return $options;
  }

}
