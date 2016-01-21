<?php

/**
 * @file
 */

class bat_event_handler_duration_field extends views_handler_field {
  function construct() {
    parent::construct();
  }

  function click_sort($order) {
    $params = $this->options['group_type'] != 'group' ? array('function' => $this->options['group_type']) : array();
    $this->query->add_orderby(NULL, NULL, $order, $this->field_alias, $params);
  }

  function query() {
    $this->ensure_my_table();

    $this->field_alias = $this->table_alias . '_duration';

    $alias = $this->field_alias;
    $counter = 0;
    while (!empty($this->query->fields[$this->field_alias])) {
      $this->field_alias = $alias . '_' . ++$counter;
    }

    // Add the field.
    $params = $this->options['group_type'] != 'group' ? array('function' => $this->options['group_type']) : array();
    $this->query->add_field(NULL, 'TIMESTAMPDIFF(SECOND, ' . $this->table_alias . '.start_date, ' . $this->table_alias . '.end_date)', $this->field_alias, $params);

    $this->add_additional_fields();
  }

  function render($values) {
    $value = $values->{$this->field_alias};
    $value += 60;

    return $this->sanitize_value(format_interval($value));
  }
}
