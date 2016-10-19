<?php

/**
 * @file
 * Contains a Views filter handler to take care of displaying the correct label
 * for unit bundles.
 */

namespace Drupal\bat_unit\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\ManyToOne;

/**
 * @ViewsFilter("bat_unit_handler_type_id_filter")
 */
class BatUnitHandlerTypeIdFilter extends ManyToOne {

  public function getValueOptions() {
    $types = bat_unit_get_types();

    $options = [];
    foreach ($types as $type) {
      $options[$type->id()] = $type->label();
    }

    $this->valueOptions = $options;

    return $this->valueOptions;
  }

}
