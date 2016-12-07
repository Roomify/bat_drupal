<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying deletes links
 * as fields.
 */

/**
 *
 */
class bat_unit_handler_delete_link_field extends bat_unit_handler_link_field {

  /**
   * {@inheritdoc}
   */
  public function construct() {
    parent::construct();
    $this->additional_fields['type'] = 'type';
  }

  /**
   * {@inheritdoc}
   */
  public function render($values) {
    $type = $values->{$this->aliases['type']};

    // Creating a dummy unit to check access against.
    $dummy_unit = bat_unit_create(array('type' => $type));
    if (!bat_unit_access('delete', $dummy_unit)) {
      return;
    }

    $text = !empty($this->options['text']) ? $this->options['text'] : t('delete');
    $unit_id = $values->{$this->aliases['unit_id']};

    $options = array();
    if (!empty($this->options['destination'])) {
      $options = array('query' => drupal_get_destination());
    }

    return l($text, 'admin/bat/units/unit/' . $unit_id . '/delete', $options);
  }

}
