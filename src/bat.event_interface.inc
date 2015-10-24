<?php

namespace Drupal/bat;


/**
 * Class BatEventInterface
 * The basic BAT event interface.
 */
interface BatEventInterface {
  /**
   * Returns the booking end day.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The day formatted string.
   */
  public function endDay($format = 'j');

  /**
   * Checks if the event starts and ends in the same month.
   *
   * @return bool
   *   TRUE if the event starts and ends in the same month, FALSE otherwise
   */
  public function sameMonth();

  /**
   * Checks if the event starts and ends in the same year.
   *
   * @return bool
   *   TRUE if the event starts and ends in the same year, FALSE otherwise
   */
  public function sameYear();

  /**
   * Returns the booking start day.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The day formatted string.
   */
  public function startDay($format = 'j');

  /**
   * Takes a single event that spans several months and transforms it to
   * monthly events - this assumes that the event is contained within a year
   *
   * @return BatEventInterface[]
   *   The event split if is necessary, a single item array otherwise.
   */
  public function transformToMonthlyEvents();

  /**
   * Takes an event that spans several years and transforms it to yearly events
   *
   * @return BatEventInterface[]
   *   The event split if is necessary, a single item array otherwise.
   */
  public function transformToYearlyEvents();

  /**
   * Returns the booking end month.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The month formatted string.
   */
  public function endMonth($format = 'n');

  /**
   * Returns the booking start month.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The month formatted string.
   */
  public function startMonth($format = 'n');

  /**
   * Returns the booking end year.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The year formatted string.
   */
  public function endYear($format = 'Y');

  /**
   * Returns the booking start year.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The year formatted string.
   */
  public function startYear($format = 'Y');

  /**
   * Returns the date interval between end and start date.
   *
   * @return bool|DateInterval
   *   The interval between the end and start date.
   */
  public function diff();

}
