<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying edit links
 * as fields.
 */

/**
 *
 */
class bat_type_handler_edit_link_field extends bat_type_handler_link_field {

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

    // Creating a dummy type to check access against.
    $dummy_type = bat_type_create(array('type' => $type));
    if (!bat_type_access('update', $dummy_type)) {
      return;
    }

    $text = !empty($this->options['text']) ? $this->options['text'] : t('edit');
    $type_id = $values->{$this->aliases['type_id']};

    $options = array();
    if (!empty($this->options['destination'])) {
      $options = array('query' => drupal_get_destination());
    }

    return l($text, 'admin/bat/config/types/manage/' . $type_id . '/edit', $options);
  }

}
