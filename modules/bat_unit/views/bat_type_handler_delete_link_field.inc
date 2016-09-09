<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying deletes links
 * as fields.
 */

class bat_type_handler_delete_link_field extends bat_type_handler_link_field {

  function construct() {
    parent::construct();
    $this->additional_fields['type'] = 'type';
  }

  function render($values) {
    $type = $values->{$this->aliases['type']};

    // Creating a dummy type to check access against.
    $dummy_type = (object) array('type' => $type, 'type_id' => NULL);
    if (!bat_type_access('delete', $dummy_type)) {
      return;
    }

    $text = !empty($this->options['text']) ? $this->options['text'] : t('delete');
    $type_id = $values->{$this->aliases['type_id']};

    $options = array();
    if (!empty($this->options['destination'])) {
      $options = array('query' => drupal_get_destination());
    }

    return l($text, 'admin/bat/config/types/type/' . $type_id . '/delete', $options);
  }

}
