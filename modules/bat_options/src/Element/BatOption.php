<?php

/**
 * @file
 * Contains \Drupal\bat_options\Element\BatOption.
 */

namespace Drupal\bat_options\Element;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Symfony\Component\HttpFoundation\Request;

/**
 * @FormElement("bat_option")
 */
class BatOption extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#element_validate' => [
        [$class, 'validateBatOption'],
      ],
      '#process' => [
        [$class, 'processBatOption'],
      ],
      '#multiple' => FALSE,
      '#attached' => [
        'library' => ['bat_options/options-widget'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    return '';
  }

  public static function processBatOption(&$element, FormStateInterface $form_state, &$complete_form) {
    $parents_prefix = implode('_', $element['#parents']);

    $element['name'] = [
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#default_value' => isset($element['#default_value']['name']) ? $element['#default_value']['name'] : NULL,
      '#attributes' => [
        'class' => ['bat_options-option--name'],
      ],
    ];
    $element['quantity'] = [
      '#type' => 'select',
      '#title' => t('Quantity'),
      '#options' => array_combine(range(1, 10, 1), range(1, 10, 1)),
      '#default_value' => isset($element['#default_value']['quantity']) ? $element['#default_value']['quantity'] : NULL,
      '#description' => t('How many of this add-on should be available'),
      '#attributes' => [
        'class' => ['bat_options-option--quantity'],
      ],
    ];
    $price_options = bat_options_price_options();
    $element['operation'] = [
      '#type' => 'select',
      '#title' => t('Operation'),
      '#options' => $price_options,
      '#default_value' => isset($element['#default_value']['operation']) ? $element['#default_value']['operation'] : NULL,
      '#attributes' => [
        'class' => ['bat_options-option--operation'],
      ],
    ];
    $element['value'] = [
      '#type' => 'textfield',
      '#title' => t('Value'),
      '#size' => 10,
      '#default_value' => (isset($element['#default_value']['value']) && $element['#default_value']['value'] != 0) ? $element['#default_value']['value'] : NULL,
      '#element_validate' => [
        '\Drupal\Core\Render\Element\Number::validateNumber',
        '\Drupal\bat_options\Element\BatOption::validateValue',
      ],
      '#attributes' => [
        'class' => ['bat_options-option--value'],
      ],
      '#states' => [
        'disabled' => [
          ':input[name="' . $element['#parents'][0] . '[' . $element['#parents'][1] . '][operation]"]' => ['value' => 'no_charge'],
        ]
      ],
    ];
    $type_options = [
      BAT_OPTIONS_OPTIONAL => t('Optional'),
      BAT_OPTIONS_MANDATORY => t('Mandatory'),
      BAT_OPTIONS_ONREQUEST => t('On Request'),
    ];
    $element['type'] = [
      '#type' => 'select',
      '#title' => t('Type'),
      '#options' => $type_options,
      '#default_value' => isset($element['#default_value']['type']) ? $element['#default_value']['type'] : 'optional',
      '#attributes' => [
        'class' => ['bat_options-option--type'],
      ],
    ];

    return $element;
  }

  /**
   * Set value as 0 if empty.
   */
  public static function validateValue(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = $element['#value'];
    if ($value === '') {
      $form_state->setValue($element['#parents'], 0);
    }
  }

  /**
   * Validate function.
   */
  public static function validateBatOption(&$element, FormStateInterface $form_state, &$complete_form) {
  }

}
