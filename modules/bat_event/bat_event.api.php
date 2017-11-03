<?php

/**
 * @file
 * This file contains no working PHP code; it exists to provide additional
 * documentation for doxygen as well as to document hooks in the standard
 * Drupal manner.
 */

/**
 * Allow modules to alter results from faceted search.
 *
 * @param $units
 * @param $context
 */
function hook_bat_facets_search_results_alter(&$units, $context) {
  unset($units[0]);
}

/**
 * Allow modules to define entity types that may be referenced by Bat Events
 * and provided to the BAT library as a Unit.
 *
 * NB: Entity types returned by this hook must implement the following methods:
 *   * getEventDefaultValue
 *   * formatEventValue
 *
 * @return array $entity_types
 *   Single-dimensional array with machine names of entity types.
 */
function hook_bat_event_target_entity_types() {
  return ['bat_unit'];
}

/**
 * Allow modules to define event constraints.
 */
function hook_bat_event_constraints_info() {
  // No example.
}

/**
 * Allow modules to alter defined event constraints.
 *
 * @param $constraints_info
 */
function hook_bat_event_constraints_info_alter($constraints_info) {
  // No example.
}
