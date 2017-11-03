<?php

/**
 * @file
 * Filter to handle dates stored as a string.
 */

namespace Drupal\bat_event\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\Date;

/**
 * @ViewsFilter("bat_event_handler_date_filter")
 */
class BatEventHandlerDateFilter extends Date {

  protected function opSimple($field) {
    $query_substitutions = views_views_query_substitutions($this->view);

    $value = date('Y-m-d', intval(strtotime($this->value['value'], $query_substitutions['***CURRENT_TIME***'])));

    $this->query->addWhereExpression($this->options['group'], "$field $this->operator '$value'");
  }

  protected function opBetween($field) {
    // Use the substitutions to ensure a consistent timestamp.
    $query_substitutions = views_views_query_substitutions($this->view);
    $a = date('Y-m-d', intval(strtotime($this->value['min'], $query_substitutions['***CURRENT_TIME***'])));
    $b = date('Y-m-d', intval(strtotime($this->value['max'], $query_substitutions['***CURRENT_TIME***'])));

    // This is safe because we are manually scrubbing the values.
    // It is necessary to do it this way because $a and $b are formulas when using an offset.
    $operator = strtoupper($this->operator);
    $this->query->addWhereExpression($this->options['group'], "$field $operator '$a' AND '$b'");
  }

}
