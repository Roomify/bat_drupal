<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying deletes links
 * as fields
 */

class bat_event_handler_delete_link_field extends bat_event_handler_link_field {
  function construct() {
    parent::construct();
    $this->additional_fields['type'] = 'type';
  }

  function render($values) {
    $type = $values->{$this->aliases['type']};

    // Creating a dummy unit to check access against.
    $dummy_event = (object) array('type' => $type, 'event_id' => NULL);
    if (!bat_event_access('delete', $dummy_event)) {
      return;
    }

    $text = !empty($this->options['text']) ? $this->options['text'] : t('delete');
    $event_id = $values->{$this->aliases['event_id']};

    return l($text, 'admin/bat/events/event/' . $event_id . '/delete');
  }
}
