<?php

/**
 * @file
 * This file contains no working PHP code; it exists to provide additional
 * documentation for doxygen as well as to document hooks in the standard
 * Drupal manner.
 */

/**
 *
 */
function hook_bat_facets_search_results_alter(&$units, $context) {
	unset($units[0]);
}
