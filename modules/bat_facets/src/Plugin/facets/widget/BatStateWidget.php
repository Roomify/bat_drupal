<?php

namespace Drupal\bat_facets\Plugin\facets\widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\Plugin\facets\widget\LinksWidget;
use Drupal\facets\FacetInterface;

/**
 * @FacetsWidget(
 *   id = "bat_state",
 *   label = @Translation("BAT State"),
 *   description = @Translation("A configurable widget for BAT"),
 * )
 */
class BatStateWidget extends LinksWidget {

  /**
   * The facet the widget is being built for.
   *
   * @var \Drupal\facets\FacetInterface
   */
  protected $facet;

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $this->facet = $facet;

    $build = \Drupal::formBuilder()->getForm('Drupal\bat_facets\Form\FacetsAvailabilityForm');

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, $config) {
    $widget_configs = !is_null($config) ? $config->get('widget_configs') : [];

    $widget_configs += ['event_type' => '', 'state' => '', 'first_state' => '', 'second_state' => ''];

    $event_types_options = array();
    $event_types = bat_event_get_types();
    foreach ($event_types as $event_type) {
      $event_types_options[$event_type->id()] = $event_type->label();
    }

    $form['#prefix'] = '<div id="bat-facet">';
    $form['#suffix'] = '</div>';

    $form['event_type'] = array(
      '#type' => 'select',
      '#title' => t('Event type'),
      '#options' => $event_types_options,
      '#default_value' => $widget_configs['event_type'],
      '#ajax' => array(
        'callback' => '::eventTypeChange',
        'wrapper' => 'bat-facet',
      ),
    );

    if ($form_state->getValue('event_type')) {
      $ev_type = $form_state->getValue('event_type');
    }
    else {
      $ev_types = array_keys($event_types_options);
      $ev_type = reset($ev_types);
    }

    if ($event_types[$ev_type]->getFixedEventStates()) {
      $state_options = bat_unit_state_options($ev_type);

      $form['state'] = array(
        '#type' => 'select',
        '#title' => t('Event State'),
        '#options' => $state_options,
        '#default_value' => $widget_configs['state'],
      );
    }
    else {
      $form['first_state'] = array(
        '#type' => 'textfield',
        '#title' => t('First state'),
        '#size' => 10,
        '#prefix' => '<div class="container-inline">',
        '#default_value' => $widget_configs['first_state'],
      );

      $form['second_state'] = array(
        '#type' => 'textfield',
        '#title' => t('Second state'),
        '#size' => 10,
        '#suffix' => '</div>',
        '#default_value' => $widget_configs['second_state'],
      );
    }

    return $form;
  }

  /**
   * Ajax callback when change 'Event type'.
   */
  function eventTypeChange(array $form, FormStateInterface $form_state) {
    return $form;
  }

}
