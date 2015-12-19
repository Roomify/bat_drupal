<?php

/**
 * @file
 * Class BatEvent
 */

namespace Drupal\bat;

abstract class BatAbstractEvent implements BatEventInterface {

  /**
   * The booking unit the event is relevant to
   * @var int
   */
  public $unit_id;

  /**
   * The start date for the event.
   *
   * @var DateTime
   */
  public $start_date;

  /**
   * The end date for the event.
   *
   * @var DateTime
   */
  public $end_date;

  /**
   * The value associated with this event.
   * This can represent an availability state or a pricing value
   *
   * @var int
   */
  public $value;

  /**
   * Returns the value.
   *
   * @return int
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Set the value.
   *
   * @param int $value
   */
  public function setValue($value) {
    $this->value = $value;
  }

  /**
   * Returns the unit id.
   *
   * @return int
   */
  public function getUnitId() {
    return $this->unit_id;
  }

  /**
   * Set the unit id.
   *
   * @param int $unit_id
   */
  public function setUnitId($unit_id) {
    $this->unit_id = $unit_id;
  }

  /**
   * Returns the start date.
   *
   * @return DateTime
   */
  public function getStartDate() {
    return clone($this->start_date);
  }

  /**
   * Set the start date.
   *
   * @param DateTime $start_date
   */
  public function setStartDate(\DateTime $start_date) {
    $this->start_date = clone($start_date);
  }

  /**
   * Returns the end date.
   *
   * @return DateTime
   */
  public function getEndDate() {
    return clone($this->end_date);
  }

  /**
   * Set the end date.
   *
   * @param DateTime $end_date
   */
  public function setEndDate(\DateTime $end_date) {
    $this->end_date = clone($end_date);
  }

  /**
   * {@inheritdoc}
   */
  public function startDay($format = 'j') {
    return $this->start_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function endDay($format = 'j') {
    return $this->end_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function startMonth($format = 'n') {
    return $this->start_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function endMonth($format = 'n') {
    return $this->end_date->format($format);
  }

  /**
   *{@inheritdoc)
   */
  public function endMonthDate(\DateTime $date){
    // The time is added so that the end date is included
    $date_format = $date->format('Y-n-t 23:59:59');
    return new \DateTime($date_format);
  }

  /**
   * {@inheritdoc}
   */
  public function startYear($format = 'Y') {
    return $this->start_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function endYear($format = 'Y') {
    return $this->end_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function startWeek($format = 'W') {
    return $this->start_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function endWeek($format = 'W') {
    return $this->end_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function startHour($format = 'G') {
    return $this->start_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function endHour($format = 'G') {
    return $this->end_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function startMinute($format = 'i') {
    return $this->start_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function endMinute($format = 'i') {
    return $this->end_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function isFirstMonth($date) {
    if ($date->format("n") == $this->startMonth() && $date->format("Y") == $this->startYear()) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isLastMonth($date) {
    if ($date->format("n") == $this->endMonth() && $date->format("Y") == $this->endYear()) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  public function isFirstDay($date){
    if (($date->format('j') == $this->startDay()) && ($this->isFirstMonth($date))) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  public function isFirstHour($date) {
    if ($date->format('G') == $this->startHour() && $this->isFirstDay($date)){
      return TRUE;
    } else {
      return FALSE;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function isSameYear() {
    if ($this->startYear() == $this->endYear()) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isSameMonth() {
    if (($this->startMonth() == $this->endMonth()) && $this->isSameYear()) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isSameDay() {
    if (($this->startDay() == $this->endDay()) && $this->isSameMonth()) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isSameHour() {
    if (($this->startHour() == $this->endHour()) && $this->sameDay()) {
      return TRUE;
    }
    return FALSE;
  }


  /**
   * {@inheritdoc}
   */
  public function diff() {
    $interval = $this->start_date->diff($this->end_date);
    return $interval;
  }

  /**
   * Based on the start and end dates of the event it creates the appropriate granular events
   * and adds them to the $itemized array.
   *
   * @param array $itemized
   * @return array
   */
  public function getDayGranural($itemized = array()){
    $interval = new \DateInterval('PT1M');
    // Creates a date period that starts form the start event to the end of the first day and split tim hourly
    if ($this->isSameDay()) {
      $period = new \DatePeriod($this->start_date, $interval, $this->end_date);
      $itemized = $this->createGranuralEvents($period, $this->start_date);
    } else {
      // Start day
      $start_period = new \DatePeriod($this->start_date, $interval, new \DateTime($this->start_date->format("Y-n-j 23:59:59")));
      $itemized_start = $this->createGranuralEvents($start_period, $this->start_date);
      $itemized[$this->start_date->format('Y')][$this->start_date->format('n')]['d'. $this->start_date->format('j')]
        = $itemized_start[$this->start_date->format('Y')][$this->start_date->format('n')]['d'. $this->start_date->format('j')];
      // End day
      $end_period = new \DatePeriod(new \DateTime($this->end_date->format("Y-n-j 00:00:00")), $interval, $this->end_date);
      $itemized_end = $this->createGranuralEvents($end_period, new \DateTime($this->end_date->format("Y-n-j 00:00:00")));
      dpm($itemized_end);
      $itemized[$this->end_date->format('Y')][$this->end_date->format('n')]['d'. $this->end_date->format('j')] =
         $itemized_end[$this->end_date->format('Y')][$this->end_date->format('n')]['d'. $this->end_date->format('j')];
    }
    return $itemized;
  }

  /**
   * Given a DatePeriod it transforms it in hours and minutes. Used to break the first and
   * last days of an event into more granular events.
   *
   * @param \DatePeriod $period
   * @return array
   */
  public function createGranuralEvents(\DatePeriod $period, \DateTime $period_start) {
    $interval = new \DateInterval('PT1M');
    $itemized = array();

    $counter = (int)$period_start->format('i');
    $start_minute = $counter;
    foreach($period as $minute){
      $itemized[$minute->format('Y')][$minute->format('n')]['d'. $minute->format('j')]['h'. $minute->format('G')]['m' .$minute->format('i')] = $this->getValue();
      $counter++;

      if ($counter == 60 && $start_minute!==0) {
        // Not a real hour - leave as is and move on
        $counter = 0;
        $start_minute = 0;
      } elseif ($counter == 60 && $start_minute == 0) {
        // Did a real whole hour so initialize the hour
        $itemized[$minute->format('Y')][$minute->format('n')]['d' . $minute->format('j')]['h' . $minute->format('G')] = $this->getValue();
        $counter = 0;
        $start_minute = 0;
      }
    }

    return $itemized;
  }

  /**
   * Transforms the event is a breakdown of days, hours and minutes with associated states.
   *
   * @return array
   */
  public function itemizeEvent() {
    // The largest interval we deal with are months (a row in the *_state/*_event tables)
    $interval = new \DateInterval('P1M');

    $daterange = new \DatePeriod($this->start_date, $interval ,$this->end_date);

    $itemized = array();

    // Cycle through each month
    foreach($daterange as $date){

      $year = $date->format("Y");
      $dayinterval = new \DateInterval('P1D');
      $dayrange = null;

      // Handle the first month
      if ($this->isFirstMonth($date)) {
        // If we are in the same month the end date is the end date of the event
        if ($this->isSameMonth()) {
          $dayrange = new \DatePeriod($this->start_date, $dayinterval, new \DateTime($this->end_date->format("Y-n-j 23:59:59")));
        } else { // alternatively it is the last day of the start month
          $dayrange = new \DatePeriod($this->start_date, $dayinterval, $this->endMonthDate($this->start_date));
        }
        foreach ($dayrange as $day) {
          $itemized[$year][$day->format('n')]['d' . $day->format('j')] = $this->getValue();
        }
      }

      // Handle the last month (will be skipped if event is same month)
      elseif ($this->isLastMonth($date)){
        $dayrange = new \DatePeriod(new \DateTime($date->format("Y-n-1")), $dayinterval, $this->end_date);
        foreach ($dayrange as $day) {
          $itemized[$year][$day->format('n')]['d' . $day->format('j')] = $this->getValue();
        }
      }

      // We are in an in-between month - just cycle through and set dates (time on end date set to ensure it is included)
      else {
        $dayrange = new \DatePeriod(new \DateTime($date->format("Y-n-1")), $dayinterval, new \DateTime($date->format("Y-n-t 23:59:59")));
        foreach ($dayrange as $day) {
          $itemized[$year][$day->format('n')]['d' . $day->format('j')] = $this->getValue();
        }
      }
    }
    //Add granural info in
    $itemized = $this->getDayGranural($itemized);
    return $itemized;
  }

}
