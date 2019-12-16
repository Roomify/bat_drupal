<?php

namespace Drupal\bat_calendar_reference;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Entity\EntityAutocompleteMatcher;

/**
 * Matcher class to get autocompletion results for entity reference.
 */
class BatUnitAutocompleteMatcher extends EntityAutocompleteMatcher {

  /**
   * {@inheritdoc}
   */
  public function getMatches($target_type, $selection_handler, $selection_settings, $string = '') {
    $matches = [];

    $options = [
      'target_type' => $target_type,
      'handler' => $selection_handler,
      'handler_settings' => $selection_settings,
    ];
    $handler = $this->selectionManager->getInstance($options);

    if (!empty($string)) {
      // Get an array of matching entities.
      $match_operator = !empty($selection_settings['match_operator']) ? $selection_settings['match_operator'] : 'CONTAINS';
      $entity_labels = $handler->getReferenceableEntities($string, $match_operator, 10);

      // Loop through the entities and convert them into autocomplete output.
      foreach ($entity_labels as $values) {
        foreach ($values as $entity_id => $label) {
          $key = "$label ($entity_id)";
          // Strip things like starting/trailing white spaces, line breaks and
          // tags.
          $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
          // Names containing commas or quotes must be wrapped in quotes.
          $key = Tags::encode($key);

          if (isset($selection_settings['unit_types'])) {
            $unit = bat_unit_load($entity_id);

            if (in_array($unit->getUnitTypeId(), $selection_settings['unit_types'])) {
              $matches[] = ['value' => $key, 'label' => $label];
            }
          }
          else {
            $matches[] = ['value' => $key, 'label' => $label];
          }
        }
      }
    }

    return $matches;
  }

}
