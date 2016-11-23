<?php

/**
 * @file
 */

/**
 *
 */
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
  public function settingsForm(&$form, &$form_state) {
    parent::settingsForm($form, $form_state);
    $settings = $this->settings->settings;

    unset($form['widget']['widget_settings']['links'][$this->id]['soft_limit']);
    unset($form['widget']['widget_settings']['links'][$this->id]['show_expanded']);
    unset($form['widget']['widget_settings'][$this->id]['facet_more_text']);

    $event_types_options = array();
    $event_types = bat_event_get_types();
    foreach ($event_types as $event_type) {
      $event_types_options[$event_type->type] = $event_type->label;
    }

    $form['widget']['widget_settings']['bat_facets']['#prefix'] = '<div id="bat-facet">';
    $form['widget']['widget_settings']['bat_facets']['#suffix'] = '</div>';

    $form['widget']['widget_settings']['bat_facets'][$this->id]['event_type'] = array(
      '#type' => 'select',
      '#title' => t('Event type'),
      '#options' => $event_types_options,
      '#default_value' => isset($settings['event_type']) ? $settings['event_type'] : array(),
      '#ajax' => array(
        'callback' => 'bat_facets_event_type_change',
        'wrapper' => 'bat-facet',
      ),
      '#states' => array(
        'visible' => array(
          'select[name="widget"]' => array('value' => $this->id),
        ),
      ),
    );

    if (isset($form_state['values']['event_type'])) {
      $ev_type = $form_state['values']['event_type'];
    }
    else {
      $ev_types = array_keys($event_types_options);
      $ev_type = reset($ev_types);
    }

    if ($event_types[$ev_type]->fixed_event_states) {
      $state_options = bat_unit_state_options($ev_type);

      $form['widget']['widget_settings']['bat_facets'][$this->id]['states'] = array(
        '#type' => 'select',
        '#title' => t('Event States'),
        '#options' => $state_options,
        '#multiple' => TRUE,
        '#default_value' => isset($settings['states']) ? $settings['states'] : array(),
        '#states' => array(
          'visible' => array(
            'select[name="widget"]' => array('value' => $this->id),
          ),
        ),
      );
    }
    else {
      $form['widget']['widget_settings']['bat_facets'][$this->id]['first_state'] = array(
        '#type' => 'textfield',
        '#title' => t('First state'),
        '#size' => 10,
        '#prefix' => '<div class="container-inline">',
        '#default_value' => isset($settings['states']) ? $settings['first_state'] : '',
      );

      $form['widget']['widget_settings']['bat_facets'][$this->id]['second_state'] = array(
        '#type' => 'textfield',
        '#title' => t('Second state'),
        '#size' => 10,
        '#suffix' => '</div>',
        '#default_value' => isset($settings['states']) ? $settings['second_state'] : '',
      );
    }
  }

}

/**
 * Ajax callback when change 'Event type'.
 */
function bat_facets_event_type_change($form, &$form_state) {
  return $form['widget']['widget_settings']['bat_facets'];
}
