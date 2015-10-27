<?php

/**
 * @file
 * BatBooking agent filter interfaces and base implementations.
 */

use Drupal\bat_availability\UnitCalendar;

/**
 * An availability agent filter receives a set of units and applies a filter
 * to them returning the remainder.
 */
interface AvailabilityAgentFilterInterface {

  /**
   * Applies the filter operation to the units in the filter.
   *
   * @return array|int
   *   Units remaining after the filter, error code otherwise.
   */
  public function applyFilter();

  /**
   * Returns a list of parameters to add to the search array.
   *
   * @return array
   *   List of parameters provided by this filter.
   */
  public static function availabilitySearchParameters();

  /**
   * Adds necessary form elements to Availability search form.
   *
   * @param array $form
   *   The Availability search form array.
   * @param array $form_state
   *   The Availability search form state array.
   */
  public static function availabilitySearchForm(&$form, &$form_state);

  /**
   * Specific validation callback for Availability search form.
   *
   * @param array $form
   *   The Availability search form array.
   * @param array $form_state
   *   The Availability search form state array.
   */
  public static function availabilitySearchFormValidate(&$form, &$form_state);

  /**
   * Adds necessary form elements to Change availability search form.
   *
   * @param array $form
   *   The Change availability search form array.
   * @param array $form_state
   *   The Change availability search form state array.
   */
  public static function availabilityChangeSearchForm(&$form, &$form_state);

  /**
   * Specific validation callback for Change availability search form.
   *
   * @param array $form
   *   The Change availability search form array.
   * @param array $form_state
   *   The Change availability search form state array.
   */
  public static function availabilityChangeSearchFormValidate(&$form, &$form_state);

}

/**
 * Abstract class implementing AvailabilityAgentFilterInterface.
 */
abstract class AvailabilityAgentFilterBase implements AvailabilityAgentFilterInterface {

  /**
   * Set of bookable units to filter through.
   *
   * @var array
   */
  protected $units;

  /**
   * Set of filter parameters.
   *
   * @var array
   */
  protected $parameters;

  /**
   * Builds a new AvailabilityAgentFilter object.
   *
   * @param array $units
   *   Set of bookable units to filter through.
   * @param array $parameters
   *   Set of filter parameters.
   */
  public function __construct(array $units, array $parameters) {
    $this->units = $units;
    $this->parameters = $parameters;
  }

  /**
   * Intersects the units that passes the filter and the unit set provided.
   *
   * @param array $filtered_units
   *   The bat_units that passes the current filter.
   *
   * @return array
   *   The intersection of filtered units with the provided set.
   */
  protected function intersectUnits($filtered_units) {
    $filtered_keys = array_keys($filtered_units);
    $unit_keys = array_keys($this->units);

    $keys_units = array_intersect($filtered_keys, $unit_keys);

    $results = array();

    foreach ($keys_units as $key) {
      $results[$key] = $this->units[$key];
    }

    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public static function availabilitySearchParameters() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public static function availabilitySearchForm(&$form, &$form_state) { }

  /**
   * {@inheritdoc}
   */
  public static function availabilitySearchFormValidate(&$form, &$form_state) { }

  /**
   * {@inheritdoc}
   */
  public static function availabilityChangeSearchForm(&$form, &$form_state) { }

  /**
   * {@inheritdoc}
   */
  public static function availabilityChangeSearchFormValidate(&$form, &$form_state) { }

}

/**
 * Filter by start_date, end_date, valid_states.
 */
class AvailabilityAgentDateFilter extends AvailabilityAgentFilterBase {

  /**
   * {@inheritdoc}
   */
  public function applyFilter() {

    // Check parameters.
    $start_date = isset($this->parameters['start_date']) ? $this->parameters['start_date'] : NULL;
    $end_date = isset($this->parameters['end_date']) ? $this->parameters['end_date'] : NULL;
    $confirmed = isset($this->parameters['confirmed']) ? $this->parameters['confirmed'] : FALSE;

    // Start date and end date parameters must be set.
    if ($start_date == NULL || $end_date == NULL) {
      return $this->units;
    }

    if (isset($this->parameters['valid_states'])) {
      $valid_states = $this->parameters['valid_states'];
    }
    else {
      $valid_states = array_keys(array_filter(variable_get('bat_valid_availability_states', drupal_map_assoc(array(BAT_AVAILABLE, BAT_ON_REQUEST)))));
      $valid_states = array_merge($valid_states, array(BAT_UNCONFIRMED_BOOKINGS));
    }

    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'bat_unit')
      ->propertyCondition('bookable', 1);

    // Execute the query and collect the results.
    $results = $query->execute();

    foreach ($results['bat_unit'] as $key => $unit) {
      $unit = bat_unit_load($unit->unit_id);

      // Get a calendar and check availability.
      $rc = new UnitCalendar($unit->unit_id);
      // We need to make this based on user-set vars.
      // Rather than using $rc->stateAvailability we will get the states check
      // directly as different states will impact on what products we create.
      $states = $rc->getStates($start_date, $end_date, $confirmed);
      $state_diff = array_diff($states, $valid_states);

      if ($this->parameters['revert_valid_states']) {
        // $valid_states match completely with existing states so remove if we are looking
        // for the opposite.
        if (count($state_diff) == 0) {
          unset($results['bat_unit'][$key]);
        }
      }
      // $valid_states don't match with all existing states so remove unit.
      elseif (count($state_diff) != 0) {
          unset($results['bat_unit'][$key]);
        }
      }

    if (empty($this->units)) {
      return $results['bat_unit'];
    }
    else {
      // Computes the intersection of units and results.
      return $this->intersectUnits($results['bat_unit']);
    }

  }

}

/**
 * Filter units by unit id.
 */
class AvailabilityAgentSingleUnitFilter extends AvailabilityAgentFilterBase {

  public function applyFilter() {
    if (variable_get('bat_presentation_style') == BAT_INDIVIDUAL && isset($_GET['bat_id']) && $requested_unit = bat_unit_load($_GET['bat_id'])) {

      foreach ($this->units as $unit) {
        if ($unit->unit_id != $requested_unit->unit_id) {
          unset($this->units[$unit->unit_id]);
        }
      }
      if (empty($this->units)) {
        drupal_set_message('Unfortunately ' . $requested_unit->name . ' is not available - try other dates if possible', 'warning');
      }

    }
    return $this->units;
  }
}
