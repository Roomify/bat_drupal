<?php

/**
 * @file
 * Class AvailabilityAgentDateFilter.
 */

namespace Drupal\bat_availability;

use Drupal\bat_availability\UnitCalendar;

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

    $query = new \EntityFieldQuery();
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
