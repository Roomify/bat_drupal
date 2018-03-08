<?php

namespace Drupal\bat_event\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;

/**
 * Entity reference selection.
 *
 * @EntityReferenceSelection(
 *   id = "bat_event:state",
 *   base_plugin_label = @Translation("Bat State Selection"),
 *   label = @Translation("Bat State Selection"),
 *   entity_types = {"state"},
 *   group = "bat_event",
 *   weight = 0
 * )
 */
class StateSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $config = $this->configuration;
    $query = parent::buildEntityQuery($match, $match_operator);

    // Limit states to those associated with the current bundle.
    if ($event_type_bundle = $config['handler_settings']['event_type_bundle']) {
      $query->condition('event_type', $event_type_bundle, '=');
    }

    return $query;
  }

}
