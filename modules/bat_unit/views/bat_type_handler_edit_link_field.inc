<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying edit links
 * as fields.
 */

class bat_type_handler_edit_link_field extends bat_type_handler_link_field {
  function construct() {
    parent::construct();
    $this->additional_fields['type'] = 'type';
  }

  function render($values) {
    $type = $values->{$this->aliases['type']};

    // Creating a dummy type to check access against.
    $dummy_type = (object) array('type' => $type, 'type_id' => NULL);
    if (!bat_type_access('update', $dummy_type)) {
      return;
    }
    $text = !empty($this->options['text']) ? $this->options['text'] : t('edit');
    $type_id = $values->{$this->aliases['type_id']};

    return l($text, 'admin/bat/config/types/type/' . $type_id . '/edit');
  }
}
