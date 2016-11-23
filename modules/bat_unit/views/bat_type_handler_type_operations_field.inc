<?php

/**
 * @file
 * This field handler aggregates operations that can be done on a type
 * under a single field providing a more flexible way to present them in a view.
 */

/**
 *
 */
class bat_type_handler_type_operations_field extends views_handler_field {

  /**
   * {@inheritdoc}
   */
  public function construct() {
    parent::construct();

    $this->additional_fields['type_id'] = 'type_id';
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
    $links = menu_contextual_links('bat_unit', 'admin/bat/config/types/manage', array($this->get_value($values, 'type_id')));

    // Unset the "view" link, as this is provided separately.
    if (array_key_exists('bat_unit-view', $links)) {
      unset($links['bat_unit-view']);
    }

    if (!empty($links)) {
      return theme('links', array(
        'links' => $links,
        'attributes' => array(
          'class' => array(
            'links',
            'inline',
            'operations',
          ),
        ),
      ));
    }
  }

}
