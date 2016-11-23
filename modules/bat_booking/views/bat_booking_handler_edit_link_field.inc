<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying edit links
 * as fields.
 */

/**
 *
 */
class bat_booking_handler_edit_link_field extends views_handler_field {

  /**
   * {@inheritdoc}
   */
  public function construct() {
    parent::construct();

    $this->additional_fields['booking_id'] = 'booking_id';
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
    $text = !empty($this->options['text']) ? $this->options['text'] : t('edit');
    $booking_id = $values->{$this->aliases['booking_id']};

    return l($text, 'admin/bat/config/booking/manage/' . $booking_id, array('query' => drupal_get_destination()));
  }

}
