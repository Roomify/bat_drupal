<?php

/**
 * @file
 * Filter to handle dates stored as a string.
 */

/**
 *
 */
class bat_event_handler_date_filter extends views_handler_filter_date {

  /**
   * {@inheritdoc}
   */
  public function op_simple($field) {
    $query_substitutions = views_views_query_substitutions($this->view);

    $value = date('Y-m-d', intval(strtotime($this->value['value'], $query_substitutions['***CURRENT_TIME***'])));

    $this->query->add_where_expression($this->options['group'], "$field $this->operator '$value'");
  }

  /**
   * {@inheritdoc}
   */
  public function op_between($field) {
    // Use the substitutions to ensure a consistent timestamp.
    $query_substitutions = views_views_query_substitutions($this->view);
    $a = date('Y-m-d', intval(strtotime($this->value['min'], $query_substitutions['***CURRENT_TIME***'])));
    $b = date('Y-m-d', intval(strtotime($this->value['max'], $query_substitutions['***CURRENT_TIME***'])));

    // This is safe because we are manually scrubbing the values.
    // It is necessary to do it this way because $a and $b are formulas when using an offset.
    $operator = strtoupper($this->operator);
    $this->query->add_where_expression($this->options['group'], "$field $operator '$a' AND '$b'");
  }

}
