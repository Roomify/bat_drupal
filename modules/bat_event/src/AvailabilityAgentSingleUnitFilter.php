<?php

/**
 * @file
 * Class AvailabilityAgentSingleUnitFilter.
 */

namespace Drupal\bat_event;

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
