<?php

/**
 * @file
 * Class HourlyAvailabilityAgent.
 */

namespace Drupal\bat_hourly_availability;

/**
 *
 */
class HourlyAvailabilityAgent {

  /**
   * @var BatUnit
   */
  public $unit;

  /**
   * @var DateTime
   */
  public $date;

  /**
   * @var array
   */
  public $opening_time_event = array();

  /**
   * @param BatUnit $unit
   *
   * @param DateTime $date
   */
  public function __construct($unit, $date) {
    $this->unit = $unit;
    $this->date = $date;

    $this->setOpeningTimeEvent();
  }

  /**
   *
   */
  private function setOpeningTimeEvent() {
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
   *
   */
  public function getEvents() {
    $events = array();

    if (!empty($opening_time_event)) {
      $end_date = clone($this->date);
      $end_date->add(new DateInterval('P1D'));

      $start = $this->date->format('Y-m-d');
      $end = $end_date->format('Y-m-d');

      $query = db_select('bat_hourly_availability', 'n')
                ->fields('n', array('id', 'start_date', 'end_date', 'state'))
                ->condition('unit_id', $unit->unit_id)
                ->orderBy('start_date')
                ->where("start_date > '$start' and end_date < '$end'");
      $results = $query->execute()->fetchAll();

      foreach ($results as $result) {
        if ($result->state != BAT_AVAILABLE) {
          $start = new \DateTime($result->start_date);
          $end = new \DateTime($result->end_date);

          $events[] = new HourlyAvailabilityEvent($start, $end);
        }
      }
    }

    return $events;
  }

  /**
   *
   */
  public function getRemainingEvents($events) {
    $remaining_events = array();

    if (!empty($this->opening_time_event)) {
      $dates[] = $this->opening_time_event->start_date;

      foreach ($events as $event) {
        $dates[] = $event->start_date;
        $dates[] = $event->end_date;
      }

      $dates[] = $this->opening_time_event->end_date;

      for ($i = 0; $i < count($dates); $i = $i + 2) {
        if ($dates[$i] < $dates[$i + 1]) {
          $remaining_events[] = array(
            'start' => $dates[$i],
            'end' => $dates[$i + 1],
            'duration' => $dates[$i]->diff($dates[$i + 1]),
          );
        }
      }
    }

    return $remaining_events;
  }

}
