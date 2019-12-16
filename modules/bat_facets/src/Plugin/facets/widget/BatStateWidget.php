<?php

/**
 * @file
 * Contains \Drupal\bat_facets\Plugin\facets\widget\BatStateWidget.
 */

namespace Drupal\bat_facets\Plugin\facets\widget;

use Drupal\Core\Form\FormBuilder;
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
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $build = $this->formBuilder->getForm('Drupal\bat_facets\Form\FacetsAvailabilityForm');

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $event_types_options = [];
    $event_types = bat_event_get_types();
    foreach ($event_types as $event_type) {
      $event_types_options[$event_type->id()] = $event_type->label();
    }

    if (isset($this->getConfiguration()['event_type'])) {
      $ev_type = $this->getConfiguration()['event_type'];
    }
    else {
      $ev_types = array_keys($event_types_options);
      $ev_type = reset($ev_types);
    }

    $form['event_type'] = [
      '#type' => 'select',
      '#title' => t('Event type'),
      '#options' => $event_types_options,
      '#default_value' => $ev_type,
      '#ajax' => [
        'callback' => '::buildAjaxWidgetConfigForm',
        'wrapper' => 'facets-widget-config-form',
      ],
    ];

    if ($event_types[$ev_type]->getFixedEventStates()) {
      $state_options = bat_unit_state_options($ev_type);

      $form['state'] = [
        '#type' => 'select',
        '#title' => t('Event State'),
        '#options' => $state_options,
        '#multiple' => TRUE,
        '#default_value' => (isset($this->getConfiguration()['state'])) ? $this->getConfiguration()['state'] : '',
      ];
    }
    else {
      $form['first_state'] = [
        '#type' => 'textfield',
        '#title' => t('First state'),
        '#size' => 10,
        '#prefix' => '<div class="container-inline">',
        '#default_value' => (isset($this->getConfiguration()['first_state'])) ? $this->getConfiguration()['first_state'] : '',
      ];

      $form['second_state'] = [
        '#type' => 'textfield',
        '#title' => t('Second state'),
        '#size' => 10,
        '#suffix' => '</div>',
        '#default_value' => (isset($this->getConfiguration()['second_state'])) ? $this->getConfiguration()['second_state'] : '',
      ];
    }

    return $form;
  }

}
