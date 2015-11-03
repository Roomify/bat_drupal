<?php

/**
 * @file
 * Class BatCalendar
 */

namespace Drupal\bat;

/**
 * Handles querying and updating the availability information
 * relative to a single bookable unit.
 */
abstract class BatCalendar implements BatCalendarInterface {

  /**
   * The unit we are dealing with.
   *
   * @var int
   */
  protected $unit_id;

  /**
   * The default state for the unit if it has no specific booking.
   *
   * @var int
   */
  protected $default_state;

  /**
   * The base table where calendar data is stored.
   *
   * @var string
   */
  protected $base_table;

  /**
   * {@inheritdoc}
   */
  public abstract function updateCalendar($events);

  /**
   * {@inheritdoc}
   */
  public function addMonthEvent(BatEventInterface $event) {
    // First check if the month exists and do an update if so
    if ($this->monthDefined($event->startMonth(), $event->startYear())) {
      $partial_month_row = $this->preparePartialMonthArray($event);
      $update = db_update($this->base_table)
        ->condition('unit_id', $this->unit_id)
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
        'unit_id' => $this->unit_id,
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
  public function monthDefined($month, $year) {
    $query = db_select($this->base_table, 'a');
    $query->addField('a', 'unit_id');
    $query->addField('a', 'year');
    $query->addField('a', 'month');
    $query->condition('a.unit_id', $this->unit_id);
    $query->condition('a.year', $year);
    $query->condition('a.month', $month);
    $result = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    if (count($result) > 0) {
      return TRUE;
    }
    return FALSE;
  }

}
