<?php

/**
 * @file
 * Contains availability_event.page.inc..
 *
 * Page callback for Availability Event entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Availability Event templates.
 *
 * Default template: availability_event.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_availability_event(array &$variables) {
  // Fetch AvailabilityEvent Entity Object.
  $availability_event = $variables['elements']['#availability_event'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
