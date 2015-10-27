<?php

/**
 * @file
 * Contains the AvailabilityAgent.
 */

use Drupal\bat_availability\UnitCalendar;
use Drupal\bat_pricing\UnitPricingCalendar;

/**
 * An AvailabilityAgent provides access to the availability functionality of
 * BAT and lets you query for availability, get pricing information and create
 * products that can be bought.
 *
 * The Agent is essentially a factory creating the appropriate responses for us
 * as needed based on the requests and the current status of our bookable units.
 *
 * An Agent reasons over a single set of information regarding a booking which
 * are exposed as public variables to make it easy for us to set and or change them.
 */
class AvailabilityAgent {

  /**
   * The start date for availability search.
   *
   * @var DateTime
   */
  public $start_date;

  /**
   * The departure date
   *
   * @var DateTime
   */
  public $end_date;

  /**
   * How many booking units are we looking for.
   *
   * @var int
   */
  public $booking_units;

  /**
   * The states to consider valid for an availability search.
   *
   * @var array
   */
  public $valid_states;

  /**
   * What unit types we are looking for.
   *
   * @var array
   */
  public $unit_types;

  /**
   * Stores available units for each booking_parameters.
   *
   * @var array
   */
  public $unit_results = array();

  /**
   * Stores first valid bat combination for booking_parameters in input.
   *
   * @var array
   */
  public $valid_unit_combination = array();

  /**
   * Standard availability search returns a unit as available only if in one of the
   * valid availability states. This switch reverts the behaviour to return a
   * unit as availability if a state not defined in valid_states within the
   * date range provided. This is particularly useful if looking for unknown state
   * values within a given date range (e.g. search for any bookings within a date range).
   *
   * @var boolean
   */
  public $revert_valid_states = FALSE;


  /**
   * Construct the AvailabilityAgent instance.
   *
   * @param DateTime $start_date
   *   The start date.
   * @param DateTime $end_date
   *   The end date.
   * @param array $booking_parameters
   *   Parameters to include in the search.
   * @param int $booking_units
   *   Number of units to book.
   * @param array $valid_states
   *   Valid states to perform the availability search.
   * @param array $unit_types
   *   Unit types to perform the search.
   * @param boolean $revert_valid_states
   *  If true availability is return if states other than the valid ones exist in date range
   */
  public function __construct($start_date, $end_date, $booking_parameters = array(), $booking_units = 1, $valid_states = array(BAT_AVAILABLE, BAT_ON_REQUEST, BAT_UNCONFIRMED_BOOKINGS), $unit_types = array(), $revert_valid_states = FALSE) {
    $this->valid_states = $valid_states;
    $this->start_date = $start_date;
    // For availability purposes the end date is a day earlier than checkout.
    $this->end_date = clone($end_date);
    $this->end_date->sub(new DateInterval('P1D'));
    $this->booking_parameters = $booking_parameters;
    $this->booking_units = $booking_units;
    $this->unit_types = $unit_types;
    $this->revert_valid_states = $revert_valid_states;
  }


  /**
   * Sets the valid states for an availability search.
   *
   * Defaults are "BAT_AVAILABLE" and "BAT_ON_REQUEST"
   *
   * @param array $states
   *   The valid states to perform the search.
   */
  public function setValidStates($states = array(BAT_AVAILABLE, BAT_ON_REQUEST, BAT_UNCONFIRMED_BOOKINGS)) {
    $this->valid_states = $states;
  }


  /**
   * Searches for availability inside a set of bookable units.
   *
   * This function is used to recursively iterate over sets of units identifying
   * whether there is a solution across the sets that has at least one option in
   * each set.
   *
   * @param array $unit_results
   *   Bookable units to perform the search.
   */
  private function searchForAvailability($unit_results) {
    $unit_results_keys = array_keys($unit_results);
    $el_key = array_shift($unit_results_keys);

    if (!isset($unit_results[$el_key])) {
      return 0;
    }

    $candidate_keys = array_keys($unit_results[$el_key]);
    if (empty($candidate_keys)) {
      return 0;
    }

    foreach ($candidate_keys as $c_key) {
      $tmp_unit_results = $unit_results;

      foreach ($tmp_unit_results as $key => $value) {
        if (isset($tmp_unit_results[$key][$c_key]) && $key != $el_key) {
          unset($tmp_unit_results[$key][$c_key]);
        }
      }

      // Combination fails, rollback and try a new combination.
      if (empty($tmp_unit_results[$el_key])) {
        return 0;
      }

      $this->valid_unit_combination[] = $tmp_unit_results[$el_key][$c_key];

      unset($tmp_unit_results[$el_key]);

      if (empty($tmp_unit_results)) {
        return 1;
      }

      // Call recursively this function.
      $return = $this->searchForAvailability($tmp_unit_results);

      if ($return == 1) {
        return $return;
      }
    }
  }

  /**
   * Checks the availability.
   *
   * If valid units exist an array keyed by valid unit ids containing unit and
   * the states it holds during the requested period or a message as to what
   * caused the failure.
   *
   * @param bool $confirmed
   *   Whether include confirmed states or not.
   *
   * @return array|int
   *   Bookable units remaining after the filter, error code otherwise.
   */
  public function checkAvailability($confirmed = FALSE) {
    // Determine the types of units that qualify - the sleeping potential of the
    // sum of the units should satisfy the group size.
    // If no booking_parameters or no group size get all available units.
    if ($this->booking_parameters == array() || $this->booking_units == 0) {
      $results = $this->applyAvailabilityFilter();

      if ($results == BAT_NO_MATCH) {
        return BAT_NO_MATCH;
      }
    }
    else {
      $this->unit_results = array();

      foreach ($this->booking_parameters as $key => $parameter) {
        $adults = 0;
        $children = 0;

        $this->unit_results[$key] = $this->applyAvailabilityFilter(array(), $adults, $children, $confirmed);
      }

      if (!empty($this->unit_results)) {
        $this->valid_unit_combination = array();

        // If a valid combination exist for booking request.
        if ($this->searchForAvailability($this->unit_results) == 1) {
          $results = array();

          foreach ($this->unit_results as $result) {
            $results = $results + $result;
          }
        }
        else {
          return BAT_NO_MATCH;
        }
      }
      else {
        return BAT_NO_MATCH;
      }
    }

    // Of the units that fit the criteria lets see what availability we have.
    $units = $this->getUnitsByPriceType($results);

    if (count($units) == 0) {
      return BAT_NO_MATCH;
    }
    else {
      return $units;
    }
  }

  /**
   * Returns availability for a specific unit.
   *
   * @param int $unit_id
   *   Bookable unit to check availability for.
   * @param array $price_modifiers
   *   Price modifiers to apply.
   *
   * @return array|int
   *   Bookable unit if available, error code otherwise.
   */
  public function checkAvailabilityForUnit($unit_id, $price_modifiers = array()) {
    // Load the unit.
    $unit = bat_unit_load($unit_id);

    $units = $this->getUnitsByPriceType(array($unit_id => $unit), $price_modifiers);
    $units = array_pop($units);
    $units = array_pop($units);

    if (count($units) == 0) {
      return BAT_NO_MATCH;
    }
    else {
      return $units;
    }
  }

  /**
   * Applies the availability filter against a set of bookable units.
   *
   * @param array $units
   *   Set of bookable units to filter.
   * @param int $adults
   *   Number of adults.
   * @param int $children
   *   Number of children.
   * @param bool $confirmed
   *   Whether include confirmed states or not.
   *
   * @return array|int
   *   bookable units remaining after the filter, error code otherwise.
   */
  protected function applyAvailabilityFilter($units = array(), $adults = 0, $children = 0, $confirmed = FALSE) {
    // Apply AvailabilityAgentSingleUnitFilter.
    $av_singleunitfilter = new AvailabilityAgentSingleUnitFilter($units, array('start_date' => $this->start_date, 'end_date' => $this->end_date));
    $units = $av_singleunitfilter->applyFilter();

    // Apply AvailabilityAgentDateFilter.
    $av_datefilter = new AvailabilityAgentDateFilter($units, array('start_date' => $this->start_date, 'end_date' => $this->end_date, 'valid_states' => $this->valid_states, 'confirmed' => $confirmed, 'revert_valid_states' => $this->revert_valid_states));
    $units = $av_datefilter->applyFilter();

    if (empty($units)) {
      return array();
    }

    ctools_include('plugins');
    $filters = ctools_get_plugins('bat_booking', 'availabilityagent_filter');

    foreach ($filters as $filter) {
      $class = ctools_plugin_get_class($filter, 'handler');
      $object_filter = new $class($units, array('start_date' => $this->start_date, 'end_date' => $this->end_date, 'group_size' => $adults, 'group_size_children' => $children, 'unit_types' => $this->unit_types, 'valid_states' => $this->valid_states, 'confirmed' => $confirmed));

      $units = $object_filter->applyFilter();
    }

    return $units;
  }

  /**
   * Returns the units array in a specific format based on price.
   *
   * @param array $results
   *   Units to sort.
   * @param array $price_modifiers
   *   Price modifiers.
   *
   * @return array
   *   Units in a price based structure.
   */
  protected function getUnitsByPriceType($results, $price_modifiers = array()) {
    $units = array();

    if (count($results) > 0) {
      foreach ($results as $unit) {
        // Get the actual entity.
        $unit = bat_unit_load($unit->unit_id);

        // Get a calendar and check availability.
        $rc = new UnitCalendar($unit->unit_id);
        // We need to make this based on user-set vars.
        // Rather than using $rc->stateAvailability we will get the states check
        // directly as different states will impact on what products we create.
        $states = $rc->getStates($this->start_date, $this->end_date);

        // Calculate the price as well to add to the array.
        $temp_end_date = clone($this->end_date);
        $temp_end_date->add(new DateInterval('P1D'));

        $booking_info = array(
          'start_date' => clone($this->start_date),
          'end_date' => $temp_end_date,
          'unit' => $unit,
        );

        // Give other modules a chance to change the price modifiers.
        drupal_alter('bat_price_modifier', $price_modifiers, $booking_info);

        $price_calendar = new UnitPricingCalendar($unit->unit_id, $price_modifiers);

        $price_log = $price_calendar->calculatePrice($this->start_date, $this->end_date);
        $full_price = $price_log['full_price'];

        $units[$unit->type][$full_price][$unit->unit_id]['unit'] = $unit;
        $units[$unit->type][$full_price][$unit->unit_id]['price'] = $full_price;
        $units[$unit->type][$full_price][$unit->unit_id]['booking_price'] = $price_log['booking_price'];
        $units[$unit->type][$full_price][$unit->unit_id]['price_log'] = $price_log['log'];


        if (in_array(BAT_ON_REQUEST, $states)) {
          $units[$unit->type][$full_price][$unit->unit_id]['state'] = BAT_ON_REQUEST;
        }
        else {
          $units[$unit->type][$full_price][$unit->unit_id]['state'] = BAT_AVAILABLE;
        }
      }
    }

    return $units;
  }

}
