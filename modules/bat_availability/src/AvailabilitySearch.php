<?php

/**
 * @file
 * Class AvailabilitySearch.
 */

namespace Drupal\bat_availability;

use Drupal\bat\BatEvent;

/**
 * AvailabilitySearch
 *
 * 1. Given a start date, end date and acceptable set of states it will provide a
 * set of units that find themselves in that state. Search can also be limited within
 * a specifc set of units.
 */
class AvailabilitySearch {
  /**
   * The start date for availability search.
   *
   * @var DateTime
   */
  public $start_date;

  /**
   * The end date for availability search
   *
   * @var DateTime
   */
  public $end_date;

  /**
   * The states to consider valid for an availability search.
   *
   * @var array
   */
  public $valid_states;

  /**
   * The set of units to take into consideration for either search or for returning
   * results
   * @var array
   */
  public $units_to_search = array();

  /**
   * Standard availability search returns a unit as available only if in one of the
   * valid availability states. This switch reverts the behaviour to return a
   * unit as availability if a state not defined in valid_states within the
   * date range provided. This is particularly useful if looking for unknown state
   * values within a given date range (e.g. search for any events within a date range).
   *
   * @var boolean
   */
  public $revert_valid_states = FALSE;


}