<?php

/**
 * @file
 */

/**
 * Implements hook_rules_data_info().
 */
function bat_event_rules_data_info() {
  return array(
    'bat_event_state_reference' => array(
      'label' => t('state'),
      'ui class' => 'RulesDataUIEventStateReference',
      'wrap' => TRUE,
      'property info' => bat_event_state_reference_field_data_property_info(),
    ),
  );
}

/**
 * Defines a bat_event_state_reference input form for Rules.
 */
class RulesDataUIEventStateReference extends RulesDataUI implements RulesDataDirectInputFormInterface {

  /**
   * {@inheritdoc}
   */
  public static function getDefaultMode() {
    return 'input';
  }

  /**
   * {@inheritdoc}
   */
  public static function inputForm($name, $info, $settings, RulesPlugin $element) {
    $settings += array($name => isset($info['default value']) ? $info['default value'] : array('state' => ''));
    $value = $settings[$name];

    $options = array();
    foreach (bat_event_get_states() as $state) {
      $options[$state['machine_name']] = $state['label'] . ' (' . $state['id'] . ')';
    }

    $form[$name]['state'] = array(
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => isset($value['state']) ? $value['state'] : '',
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function render($value) {
    return array(
      'content' => array(
        '#markup' => $value['state'],
      ),
    );
  }

}
