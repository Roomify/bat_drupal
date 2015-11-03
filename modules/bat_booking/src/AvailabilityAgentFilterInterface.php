<?php

/**
 * @file
 * Interface AvailabilityAgentFilterInterface.
 */

namespace Drupal\bat_booking;

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
