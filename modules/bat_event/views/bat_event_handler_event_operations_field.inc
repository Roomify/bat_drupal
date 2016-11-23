<?php

/**
 * @file
 * This field handler aggregates operations that can be done on a event
 * under a single field providing a more flexible way to present them in a view
 */

/**
 *
 */
class bat_event_handler_event_operations_field extends views_handler_field {

  /**
   * {@inheritdoc}
   */
  public function construct() {
    parent::construct();

    $this->additional_fields['event_id'] = 'event_id';
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensure_my_table();
    $this->add_additional_fields();
  }

  /**
   * {@inheritdoc}
   */
  public function render($values) {
    $links = menu_contextual_links('bat_event', 'admin/bat/events/event', array($this->get_value($values, 'event_id')));
    if (!empty($links)) {
      return theme('links', array(
        'links' => $links,
        'attributes' => array(
          'class' => array('links',
            'inline',
            'operations',
          ),
        ),
      ));
    }
  }

}
