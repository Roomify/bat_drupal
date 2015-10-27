<?php

use Drupal\bat_availability\UnitCalendar;


$plugin = array(
  'label' => t('Availability Hourly Filter'),
  'handler' => array(
    'class' => 'AvailabilityAgentHourlyFilter',
  ),
);

class AvailabilityAgentHourlyFilter extends AvailabilityAgentFilterBase {

  /**
   * {@inheritdoc}
   */
  public function applyFilter() {
    if (isset($this->parameters['valid_states'])) {
      $valid_states = $this->parameters['valid_states'];
      $valid_states = array_merge($valid_states, array(BAT_HOURLY_BOOKED));
    }
    else {
      $valid_states = array_keys(array_filter(variable_get('bat_valid_availability_states', drupal_map_assoc(array(BAT_AVAILABLE, BAT_ON_REQUEST)))));
      $valid_states = array_merge($valid_states, array(BAT_UNCONFIRMED_BOOKINGS, BAT_HOURLY_BOOKED));
    }

    $start_date = isset($this->parameters['start_date']) ? $this->parameters['start_date'] : NULL;
    $end_date = isset($this->parameters['end_date']) ? $this->parameters['end_date'] : NULL;
    $confirmed = isset($this->parameters['confirmed']) ? $this->parameters['confirmed'] : FALSE;

    $start = $start_date->format('Y-m-d H:i:s');
    $end = $end_date->format('Y-m-d H:i:s');

    $query = db_select('bat_hourly_availability', 'n')
            ->fields('n', array('id', 'unit_id', 'start_date', 'end_date', 'state'))
            ->where("start_date between '$start' and '$end' or end_date between '$start' and '$end'");
    $hourly_availability = $query->execute()->fetchAll();

    $not_available_units = array();
    foreach ($hourly_availability as $availability) {
      if (!in_array($availability->state, $valid_states)) {
        $not_available_units[] = $availability->unit_id;
      }
    }

    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'bat_unit')
      ->propertyCondition('type', $this->parameters['unit_types'], 'IN')
      ->propertyCondition('bookable', 1);

    // Execute the query and collect the results.
    $bat_units = $query->execute();

    $results = array();
    foreach ($bat_units['bat_unit'] as $key => $unit) {
      if (!in_array($unit->unit_id, $not_available_units)) {
        $rc = new UnitCalendar($unit->unit_id);

        $states = $rc->getStates($start_date, $end_date, $confirmed);
        $state_diff = array_diff($states, $valid_states);

        if (empty($state_diff)) {
          $results[$unit->unit_id] = bat_unit_load($unit->unit_id);
        }
      }
    }

    return $results;
  }

}
