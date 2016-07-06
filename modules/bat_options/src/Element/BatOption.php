<?php

/**
 * @file
 * Contains \Drupal\bat_options\Element\BatOption.
 */

namespace Drupal\bat_options\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;

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
      '#process' => [
        [$class, 'processBatOption'],
      ],
      '#element_validate' => [
        [$class, 'validateBatOption'],
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
    return $return;
  }

  public static function processBatOption(&$element, FormStateInterface $form_state, &$complete_form) {
    $parents_prefix = implode('_', $element['#parents']);

    // Generate a unique wrapper HTML ID.
    $ajax_wrapper_id = Html::getUniqueId('ajax-wrapper');

    $element['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#default_value' => isset($items[$delta]->name) ? $items[$delta]->name : NULL,
      '#attributes' => array(
        'class' => array('bat_options-option--name'),
      ),
    );
    $element['quantity'] = array(
      '#type' => 'select',
      '#title' => t('Quantity'),
      '#options' => array_combine(range(1, 10, 1), range(1, 10, 1)),
      '#default_value' => isset($items[$delta]->quantity) ? $items[$delta]->quantity : NULL,
      '#description' => t('How many of this add-on should be available'),
      '#attributes' => array(
        'class' => array('bat_options-option--quantity'),
      ),
    );
    $price_options = bat_options_price_options();
    $element['operation'] = array(
      '#type' => 'select',
      '#title' => t('Operation'),
      '#options' => $price_options,
      '#default_value' => isset($items[$delta]->operation) ? $items[$delta]->operation : NULL,
      '#attributes' => array(
        'class' => array('bat_options-option--operation'),
      ),
    );
    $element['value'] = array(
      '#type' => 'textfield',
      '#title' => t('Value'),
      '#size' => 10,
      '#default_value' => (isset($items[$delta]->value) && $items[$delta]->value != 0) ? $items[$delta]->value : NULL,
      '#element_validate' => array('\Drupal\Core\Render\Element\Number::validateNumber', '\Drupal\bat_options\Plugin\Field\FieldWidget\BatOptionsCombined::elementValueValidate'),
      '#attributes' => array(
        'class' => array('bat_options-option--value'),
      ),
      '#states' => array(
        'disabled' => array(
          ':input[name="field_addons[en][' . $delta . '][operation]"]' => array('value' => 'no_charge'),
        )
      ),
    );
    $type_options = array(
      BAT_OPTIONS_OPTIONAL => t('Optional'),
      BAT_OPTIONS_MANDATORY => t('Mandatory'),
      BAT_OPTIONS_ONREQUEST => t('On Request'),
    );
    $element['type'] = array(
      '#type' => 'select',
      '#title' => t('Type'),
      '#options' => $type_options,
      '#default_value' => isset($items[$delta]->type) ? $items[$delta]->type : 'optional',
      '#attributes' => array(
        'class' => array('bat_options-option--type'),
      ),
    );

    $element['remove_button'] = array(
      '#name' => $parents_prefix . '_remove_button',
      '#type' => 'submit',
      '#value' => $element['#multiple'] ? t('Remove selected') : t('Remove'),
      '#validate' => array(),
      '#submit' => array(),
      '#limit_validation_errors' => array($element['#parents']),
      '#ajax' => array(
        'wrapper' => $ajax_wrapper_id,
      ),
    );

    // Prefix and suffix used for Ajax replacement.
    $element['#prefix'] = '<div class="field-multiple-table" id="' . $ajax_wrapper_id . '">';
    $element['#suffix'] = '</div>';

    return $element;
  }

  public static function validateBatOption(&$element, FormStateInterface $form_state, &$complete_form) {
  }

}
