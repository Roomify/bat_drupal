<?php

/**
 * @file
 * Contains \Drupal\bat_options\Plugin\Field\FieldWidget\BatOptionsCombined.
 */

namespace Drupal\bat_options\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @FieldWidget(
 *   id = "bat_options_combined",
 *   label = @Translation("Combined text field'"),
 *   field_types = {
 *     "bat_options"
 *   }
 * )
 */
class BatOptionsCombined extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $parents = $form['#parents'];

    // Load the items for form rebuilds from the field state as they might not
    // be in $form_state->getValues() because of validation limitations. Also,
    // they are only passed in as $items when editing existing entities.
    $field_state = static::getWidgetState($parents, $field_name, $form_state);
    if (isset($field_state['items'])) {
      $items->setValue($field_state['items']);
    }

    // Determine the number of widgets to display.
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    switch ($cardinality) {
      case FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED:
        $max = count($items);
        $is_multiple = TRUE;
        break;

      default:
        $max = $cardinality - 1;
        $is_multiple = ($cardinality > 1);
        break;
    }

    $title = $this->fieldDefinition->getLabel();
    $description = $this->getFilteredDescription();

    $elements = array();

    $delta = 0;
    foreach ($items as $item) {
      $element = array(
        '#title' => $title,
        '#description' => $description,
      );
      $element = $this->formSingleElement($items, $delta, $element, $form, $form_state);

      $elements[$delta] = $element;
      $delta++;
    }

    $empty_single_allowed = ($cardinality == 1 && $delta == 0);
    $empty_multiple_allowed = ($cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED || $delta < $cardinality) && !$form_state->isProgrammed();

    // Add one more empty row for new uploads except when this is a programmed
    // multiple form as it is not necessary.
    if ($empty_single_allowed || $empty_multiple_allowed) {
      // Create a new empty item.
      $items->appendItem();
      $element = array(
        '#title' => $title,
        '#description' => $description,
      );
      $element = $this->formSingleElement($items, $delta, $element, $form, $form_state);
      if ($element) {
        $element['#required'] = ($element['#required'] && $delta == 0);
        $elements[$delta] = $element;
      }
    }

    if ($is_multiple) {
      $elements['#type'] = 'details';
      $elements['#open'] = TRUE;
      $elements['#process'] = array(array(get_class($this), 'processMultiple'));
      $elements['#title'] = $title;
      $elements['#description'] = $description;
      $elements['#field_name'] = $field_name;
      $elements['#language'] = $items->getLangcode();
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element += array(
      '#type' => 'bat_option',
      '#value_callback' => array(get_class($this), 'value'),
    );

    $element['#weight'] = $delta;
    $element['#default_value'] = $items[$delta]->getValue();

    return $element;
  }

  public static function value($element, $input = FALSE, FormStateInterface $form_state) {
    return $return;
  }

  public static function processMultiple($element, FormStateInterface $form_state, $form) {
    $element['#prefix'] = '<div id="' . $element['#id'] . '-ajax-wrapper">';
    $element['#suffix'] = '</div>';

    return $element;
  }

}
