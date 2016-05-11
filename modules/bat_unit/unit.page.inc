<?php

/**
 * @file
 * Contains unit.page.inc..
 *
 * Page callback for Unit entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Unit templates.
 *
 * Default template: unit.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_unit(array &$variables) {
  // Fetch Unit Entity Object.
  $unit = $variables['elements']['#unit'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
