<?php

/**
 * @file
 */

/**
 *
 */
class bat_event_handler_event_state_filter extends views_handler_filter_in_operator {

  /**
   * {@inheritdoc}
   */
  public function get_value_options() {
    $options = array();

    foreach (bat_event_get_states() as $state) {
      $options[$state['id']] = $state['label'];
    }

    $this->value_options = $options;

    return $this->value_options;
  }

}
