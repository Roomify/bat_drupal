<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying links to entities
 * as fields.
 */

/**
 *
 */
class bat_event_handler_link_field extends views_handler_field {

  /**
   * {@inheritdoc}
   */
  public function construct() {
    parent::construct();

    $this->additional_fields['event_id'] = 'event_id';
    $this->additional_fields['type'] = 'type';
  }

  /**
   * {@inheritdoc}
   */
  public function option_definition() {
    $options = parent::option_definition();

    $options['text'] = array('default' => '', 'translatable' => TRUE);

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $form['text'] = array(
      '#type' => 'textfield',
      '#title' => t('Text to display'),
      '#default_value' => $this->options['text'],
    );
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
    $text = !empty($this->options['text']) ? $this->options['text'] : t('view');
    $event_id = $values->{$this->aliases['event_id']};

    return l($text, 'event/' . $event_id);
  }

}
