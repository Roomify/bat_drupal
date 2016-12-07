<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying deletes links
 * as fields
 */

/**
 *
 */
class bat_event_handler_delete_link_field extends bat_event_handler_link_field {

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
    $dummy_event = bat_event_create(array('type' => $type));
    if (!bat_event_access('delete', $dummy_event)) {
      return;
    }

    $text = !empty($this->options['text']) ? $this->options['text'] : t('delete');
    $event_id = $values->{$this->aliases['event_id']};

    return l($text, 'admin/bat/events/event/' . $event_id . '/delete');
  }

}
