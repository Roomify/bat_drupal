<?php

/**
 * @file
 */

class bat_event_handler_nights_field extends views_handler_field_numeric {
  function construct() {
    parent::construct();
  }

  function click_sort($order) {
    $params = $this->options['group_type'] != 'group' ? array('function' => $this->options['group_type']) : array();
    $this->query->add_orderby(NULL, NULL, $order, $this->field_alias, $params);
  }

  function query() {
    $this->ensure_my_table();

    $this->field_alias = $this->table_alias . '_nights';

    $alias = $this->field_alias;
    $counter = 0;
    while (!empty($this->query->fields[$this->field_alias])) {
      $this->field_alias = $alias . '_' . ++$counter;
    }

    // Add the field.
    $params = $this->options['group_type'] != 'group' ? array('function' => $this->options['group_type']) : array();
    $this->query->add_field(NULL, '(TO_DAYS(' . $this->table_alias . '.end_date) - TO_DAYS(' . $this->table_alias . '.start_date))', $this->field_alias, $params);

    $this->add_additional_fields();
  }

  function render($values) {
    $value = number_format($values->{$this->field_alias}, $this->options['precision'], $this->options['decimal'], $this->options['separator']);
    return $this->sanitize_value($value);
  }
}
