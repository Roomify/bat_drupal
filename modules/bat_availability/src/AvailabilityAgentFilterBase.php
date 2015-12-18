<?php

/**
 * @file
 * Class AvailabilityAgentFilterBase.
 */

namespace Drupal\bat_availability;

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
