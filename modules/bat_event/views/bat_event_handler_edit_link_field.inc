<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying edit links
 * as fields
 */

class bat_event_handler_edit_link_field extends bat_event_handler_link_field {
  function construct() {
    parent::construct();
    $this->additional_fields['type'] = 'type';
  }

  function render($values) {
    $type = $values->{$this->aliases['type']};

    // Creating a dummy event to check access against
    $dummy_event = (object) array('type' => $type, 'event_id' => NULL);
    if (!bat_event_access('update', $dummy_event)) {
      return;
    }
    $text = !empty($this->options['text']) ? $this->options['text'] : t('edit');
    $event_id = $values->{$this->aliases['event_id']};

    return l($text, 'admin/bat/events/event/' . $event_id . '/edit');
  }
}
