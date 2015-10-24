<?php

namespace Drupal/bat;

abstract class BatEvent implements BatEventInterface {

  /**
   * The booking unit the event is relevant to
   * @var int
   */
  public $unit_id;

  /**
   * The default state for the unit if it has no specific booking.
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
   * {@inheritdoc}
   */
  public function startDay($format = 'j') {
    return $this->start_date->format($format);
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
  public function startYear($format = 'Y') {
    return $this->start_date->format($format);
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
  public function endDay($format = 'j') {
    return $this->end_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function endMonth($format = 'n') {
    return $this->end_date->format($format);
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
  public function endWeek($format = 'W') {
    return $this->end_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function diff() {
    $interval = $this->start_date->diff($this->end_date);
    return $interval;
  }

  /**
   * {@inheritdoc}
   */
  public function sameMonth() {
    if (($this->startMonth() == $this->endMonth()) && ($this->startYear() == $this->endYear())) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function sameYear() {
    if ($this->startYear() == $this->endYear()) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function transformToYearlyEvents() {
    // If same year return the event
    if ($this->sameYear()) {
      $sd = new DateTime();
      $sd->setDate($this->startYear(), $this->startMonth(), $this->startDay());
      $ed = new DateTime();
      $ed->setDate($this->endYear(), $this->endMonth(), $this->endDay());
      $be = $this->createEvent($sd, $ed);
      return array($be);
    }

    // Else split into years
    $events = array();
    for ($i = $this->startYear(); $i <= $this->endYear(); $i++) {
      $sd = new DateTime();
      $ed = new DateTime();
      if ($i == $this->startYear()) {
        $sd->setDate($i, $this->startMonth(), $this->startDay());
        $ed->setDate($i, 12, 31);
        $events[$i] = $this->createEvent($sd, $ed);
      }
      elseif ($i == $this->endYear()) {
        $sd->setDate($i, 1, 1);
        $ed->setDate($i, $this->endMonth(), $this->endDay());
        $events[$i] = $this->createEvent($sd, $ed);
      }
      else {
        $sd->setDate($i, 1, 1);
        $ed->setDate($i, 12, 31);
        $events[$i] = $this->createEvent($sd, $ed);
      }
    }

    return $events;
  }

  /**
   * {@inheritdoc}
   */
  public function transformToMonthlyEvents() {
    $events = array();
    // First we need to split into events in separate years
    if (!$this->sameYear()) {
      return FALSE;
    }
    if ($this->sameMonth()) {
      $sd = new DateTime();
      $sd->setDate($this->startYear(), $this->startMonth(), $this->startDay());
      $ed = new DateTime();
      $ed->setDate($this->endYear(), $this->endMonth(), $this->endDay());
      $be = $this->createEvent($sd, $ed);
      return array($be);
    }
    $months = bat_end_of_month_dates($this->startYear());

    for ($i = $this->startMonth(); $i <= $this->endMonth(); $i++) {
      $sd = new DateTime();
      $ed = new DateTime();
      if ($i == $this->startMonth()) {
        $sd->setDate($this->startYear() , $i, $this->startDay());
        $ed->setDate($this->startYear(), $i, $months[$i]);
        $events[$i] = $this->createEvent($sd, $ed);
      }
      elseif ($i == $this->endMonth()) {
        $sd->setDate($this->startYear(), $i, 1);
        $ed->setDate($this->startYear(), $i, $this->endDay());
        $events[$i] = $this->createEvent($sd, $ed);
      }
      else {
        $sd->setDate($this->startYear(), $i, 1);
        $ed->setDate($this->startYear(), $i, $months[$i]);
        $events[$i] = $this->createEvent($sd, $ed);
      }
    }
    return $events;
  }

  /**
   * Creates a new event
   *
   * @param DateTime $start_date
   *   The new event start date.
   * @param DateTime $end_date
   *   The new event end date.
   *
   * @return BatEventInterface
   *   The new event created.
   */
  protected abstract function createEvent(DateTime $start_date, DateTime $end_date);

}
