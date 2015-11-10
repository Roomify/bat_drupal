<?php

/**
 * @file
 * Contains property.page.inc..
 *
 * Page callback for Property entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Property templates.
 *
 * Default template: property.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_property(array &$variables) {
  // Fetch Property Entity Object.
  $property = $variables['elements']['#property'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
