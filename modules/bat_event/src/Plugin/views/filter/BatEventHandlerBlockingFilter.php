<?php

/**
 * @file
 */

namespace Drupal\bat_event\Plugin\views\filter;

use Drupal\views\Views;
use Drupal\views\Plugin\views\filter\BooleanOperator;

/**
 * @ViewsFilter("bat_event_handler_blocking_filter")
 */
class BatEventHandlerBlockingFilter extends BooleanOperator {

  public function construct() {
    parent::construct();

    $this->value_value = t('State');
  }

  public function getValueOptions() {
    $options = [
      'blocking' => t('Blocking'),
      'not_blocking' => t('Not blocking'),
    ];

    $this->valueOptions = $options;
  }

  public function query() {
    $this->ensureMyTable();

    if ($this->value == 'not_blocking' || $this->value == 'blocking') {
      $configuration = [
        'table' => 'bat_event__event_state_reference',
        'field' => 'entity_id',
        'left_table' => 'event',
        'left_field' => 'id',
        'type' => 'left',
      ];
      $state_reference_join = Views::pluginManager('join')->createInstance('standard', $configuration);

      $this->query->addRelationship('bat_event__event_state_reference', $state_reference_join, 'event');

      $configuration = [
        'table' => 'states',
        'field' => 'id',
        'left_table' => 'bat_event__event_state_reference',
        'left_field' => 'event_state_reference_target_id',
        'type' => 'left',
      ];
      $state_join = Views::pluginManager('join')->createInstance('standard', $configuration);

      $this->query->addRelationship('states', $state_join, 'bat_event__event_state_reference');

      if ($this->value == 'not_blocking') {
        $this->query->addWhere(1, 'states.blocking', '0', '=');
      }
      elseif ($this->value == 'blocking') {
        $this->query->addWhere(1, 'states.blocking', '1', '=');
      }
    }
  }

  public function adminSummary() {
    if ($this->isAGroup()) {
      return t('grouped');
    }
    if (!empty($this->options['exposed'])) {
      return t('exposed');
    }
    if (empty($this->valueOptions)) {
      $this->getValueOptions();
    }

    return $this->valueOptions[$this->value];
  }

}
