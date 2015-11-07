<?php

class BatStateWidget extends FacetapiWidget {

  /**
   * Execution callback.
   */
  public function execute() {
    $elements = &$this->build[$this->facet['field alias']];
    $elements = drupal_get_form('bat_facets_availability', $elements);
  }

  /**
   * Overrides FacetapiWidget::settingsForm().
   */
  function settingsForm(&$form, &$form_state) {
    parent::settingsForm($form, $form_state);
    $settings = $this->settings->settings;

    unset($form['widget']['widget_settings']['links'][$this->id]['soft_limit']);
    unset($form['widget']['widget_settings']['links'][$this->id]['show_expanded']);
    unset($form['widget']['widget_settings'][$this->id]['facet_more_text']);

    $form['widget']['widget_settings']['bat_facets'][$this->id]['states'] = array(
      '#type' => 'select',
      '#title' => 'States to return for the dates selected by the user.',
      '#options' => bat_unit_state_options(),
      '#multiple' => TRUE,
      '#default_value' => isset($settings['states']) ? $settings['states'] : array(),
      '#states' => array(
        'visible' => array(
          'select[name="widget"]' => array('value' => $this->id),
        ),
      ),
    );
  }

}
