<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying edit links
 * as fields
 */


class bat_booking_handler_unit_field extends views_handler_field {
  function construct() {
    parent::construct();

    $this->additional_fields['unit_id'] = 'unit_id';
  }

  function option_definition() {
    $options = parent::option_definition();

    $options['text'] = array('default' => '', 'translatable' => TRUE);

    return $options;
  }

  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $form['text'] = array(
      '#type' => 'textfield',
      '#title' => t('Text to display'),
      '#default_value' => $this->options['text'],
    );
  }

  function query() {
    $this->ensure_my_table();
    $this->add_additional_fields();
  }

  function render($values) {
    $unit_id = $values->{$this->aliases['unit_id']};
    $unit = bat_unit_load($unit_id);
    if (!is_object($unit)) {
      return '';
    }

    $text = !empty($this->options['text']) ? $this->options['text'] : $unit->name;

    return l($text, 'admin/bat/units/unit/' . $unit_id . '/availability');
  }
}
