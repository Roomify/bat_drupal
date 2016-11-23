<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying links to entities
 * as fields.
 */

/**
 *
 */
class bat_type_handler_link_field extends views_handler_field {

  /**
   * {@inheritdoc}
   */
  public function construct() {
    parent::construct();

    $this->additional_fields['type_id'] = 'type_id';
    $this->additional_fields['type'] = 'type';
  }

  /**
   * {@inheritdoc}
   */
  public function option_definition() {
    $options = parent::option_definition();

    $options['text'] = array('default' => '', 'translatable' => TRUE);
    $options['destination'] = array('default' => TRUE, 'bool' => TRUE);

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

    $form['destination'] = array(
      '#type' => 'checkbox',
      '#title' => t('Include destination'),
      '#description' => t('Include a "destination" parameter in the link to return the user to the original view upon completing the link action.'),
      '#default_value' => $this->options['destination'],
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
    $type_id = $values->{$this->aliases['type_id']};

    $options = array();
    if (!empty($this->options['destination'])) {
      $options = array('query' => drupal_get_destination());
    }

    return l($text, 'type/' . $type_id, $options);
  }

}
