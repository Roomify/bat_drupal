<?php

/**
 * @file
 * Class BatCalendar
 */

namespace Drupal\bat;

use Drupal\bat\BatEventInterface;

define('BAT_DAY', 'bat_day');
define('BAT_HOUR', 'bat_hour');
define('BAT_MINUTE', 'bat_minute');
define('BAT_EVENT_DAY_EVENT', 'bat_event_day_event');
define('BAT_EVENT_DAY_STATE', 'bat_event_day_state');
define('BAT_EVENT_HOUR_EVENT', 'bat_event_hour_event');
define('BAT_EVENT_HOUR_STATE', 'bat_event_hour_state');
define('BAT_EVENT_MINUTE_EVENT', 'bat_event_minute_event');
define('BAT_EVENT_MINUTE_STATE', 'bat_event_minute_state');

/**
 * Handles querying and updating the availability information
 * relative to a single bookable unit based on BAT's data structure
 */
abstract class BatCalendar implements BatCalendarInterface {

  /**
   * The unit we are dealing with.
   *
   * @var int
   */
  protected $unit_id;

  /**
   * The default value for state or event
   *
   * @var int
   */
  protected $default_state;

  /**
   * Granularity
   *
   * Irrespective of the actual values of the start and end dates we need to know
   * what level of granularity the event should be saved as. This is one of day, hour
   * and minute.
   */
  protected $granularity;

  /**
   * {@inheritdoc}
   */
  public abstract function updateCalendar($events, $events_to_remove = array(), $granularity = BAT_DAY);


  public function createDayRecords(\BatEvent $event) {

    $record = array();
    $start_year = $event->getStartYear();

    // Set the start year - since this is bound to be there
    $record[$start_year] = array();

    $interval = $event->diff;

    // Check to see if we are dealing with multiple years
    if ($interval->y > 0){
      for ($i = $start_year; $i < $event->getEndYear(); $i++) {
        // Setup the years
        $record[$i] = array();
      }
    }

    return $record;

  }

  /**
   * {@inheritdoc}
   */
  public function addMonthEvent(BatEventInterface $event) {
    // First check if the month exists and do an update if so
    if ($this->monthDefined($event)) {
      $partial_month_row = $this->preparePartialMonthArray($event);
      $update = db_update($this->base_table)
        ->condition('unit_id', $event->unit_id)
        ->condition('month', $event->startMonth())
        ->condition('year', $event->startYear())
        ->fields($partial_month_row)
        ->execute();
    }
    // Do an insert for a new month
    else {
      // Prepare the days array
      $days = $this->prepareFullMonthArray($event);
      $month_row = array(
        'unit_id' => $event->unit_id,
        'year' => $event->startYear(),
        'month' => $event->startMonth(),
      );
      $month_row = array_merge($month_row, $days);
      $insert = db_insert($this->base_table)->fields($month_row);
      $insert->execute();
    }
  }

  /**
   * Given an event it prepares the entire month array for it
   * assuming no other events in the month and days where there
   * is no event get set to the default state.
   *
   * @param BatEventInterface $event
   *   The event to process.
   *
   * @return array
   *   The days of the month states processed array.
   */
  protected abstract function prepareFullMonthArray(BatEventInterface $event);

  /**
   * Given an event it prepares a partial array covering just the days
   * for which the event is involved
   *
   * @param BatEventInterface $event
   *   The event to process.
   *
   * @return array
   *   The days of the month states processed array.
   */
  protected abstract function preparePartialMonthArray(BatEventInterface $event);

  /**
   * {@inheritdoc}
   */
  public abstract function getEvents(\DateTime $start_date, \DateTime $end_date);

  /**
   * {@inheritdoc}
   */
  public abstract function getRawDayData(\DateTime $start_date, \DateTime $end_date);

  /**
   * {@inheritdoc}
   */
  public function monthDefined($event) {
    $month = $event->startMonth();
    $year = $event->startYear();
    $unit_id = $event->unit_id;

    $query = db_select($this->base_table, 'a');
    $query->addField('a', 'unit_id');
    $query->addField('a', 'year');
    $query->addField('a', 'month');
    $query->condition('a.unit_id', $unit_id);
    $query->condition('a.year', $year);
    $query->condition('a.month', $month);
    $result = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    if (count($result) > 0) {
      return TRUE;
    }
    return FALSE;
  }

}
