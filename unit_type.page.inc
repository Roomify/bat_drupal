<?php

/**
 * @file
 * Contains unit_type.page.inc..
 *
 * Page callback for Unit type entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Unit type templates.
 *
 * Default template: unit_type.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_unit_type(array &$variables) {
  // Fetch UnitType Entity Object.
  $unit_type = $variables['elements']['#unit_type'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
