<?php

namespace Drupal\bat_options\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Number;

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
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_parents = $element['#field_parents'];
    $field_name = $element['#field_name'];
    $language = $element['#language'];

    $parents = array_merge($field_parents, array($field_name, $language, $delta));

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
      '#element_validate' => array('Number::validateNumber', '::bat_options_element_value_validate'),
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

    $element['remove'] = array(
      '#delta' => $delta,
      '#name' => implode('_', $parents) . '_remove_button',
      '#type' => 'submit',
      '#value' => t('Remove'),
      '#validate' => array(),
      '#submit' => array('::bat_options_remove_submit'),
      '#limit_validation_errors' => array(),
      '#ajax' => array(
        'path' => 'bat_options/ajax',
        'effect' => 'fade',
      ),
      '#attributes' => array(
        'class' => array('bat_options-option--remove-button'),
      ),
    );

    $element['#attached']['library'] = array('bat_options/options-widget');

    return $element;
  }

  /**
   * Submit callback to remove an item from the field UI multiple wrapper.
   *
   * When a remove button is submitted, we need to find the item that it
   * referenced and delete it. Since field UI has the deltas as a straight
   * unbroken array key, we have to renumber everything down. Since we do this
   * we *also* need to move all the deltas around in the $form_state['values'],
   * $form_state['input'], and $form_state['field'] so that user changed values
   * follow. This is a bit of a complicated process.
   */
  function bat_options_remove_submit($form, FormStateInterface $form_state) {
    $button = $form_state['triggering_element'];
    $delta = $button['#delta'];

    // Where in the form we'll find the parent element.
    $address = array_slice($button['#array_parents'], 0, -2);

    // Go one level up in the form, to the widgets container.
    $parent_element = drupal_array_get_nested_value($form, $address);
    $field_name = $parent_element['#field_name'];
    $langcode = $parent_element['#language'];
    $parents = $parent_element['#field_parents'];

    $field_state = field_form_get_state($parents, $field_name, $langcode, $form_state);

    // Go ahead and renumber everything from our delta to the last
    // item down one. This will overwrite the item being removed.
    for ($i = $delta; $i <= $field_state['items_count']; $i++) {
      $old_element_address = array_merge($address, array($i + 1));
      $new_element_address = array_merge($address, array($i));

      $moving_element = drupal_array_get_nested_value($form, $old_element_address);
      $moving_element_value = drupal_array_get_nested_value($form_state['values'], $old_element_address);
      $moving_element_input = drupal_array_get_nested_value($form_state['input'], $old_element_address);
      $moving_element_field = drupal_array_get_nested_value($form_state['field'], $old_element_address);

      // Tell the element where it's being moved to.
      $moving_element['#parents'] = $new_element_address;

      // Move the element around.
      form_set_value($moving_element, $moving_element_value, $form_state);
      drupal_array_set_nested_value($form_state['input'], $moving_element['#parents'], $moving_element_input);
      drupal_array_set_nested_value($form_state['field'], $moving_element['#parents'], $moving_element_field);
    }

    // Then remove the last item. But we must not go negative.
    if ($field_state['items_count'] > 0) {
      $field_state['items_count']--;
    }

    // Fix the weights. Field UI lets the weights be in a range of
    // (-1 * item_count) to (item_count). This means that when we remove one,
    // the range shrinks; weights outside of that range then get set to
    // the first item in the select by the browser, floating them to the top.
    // We use a brute force method because we lost weights on both ends
    // and if the user has moved things around, we have to cascade because
    // if I have items weight weights 3 and 4, and I change 4 to 3 but leave
    // the 3, the order of the two 3s now is undefined and may not match what
    // the user had selected.
    $input = drupal_array_get_nested_value($form_state['input'], $address);
    // Sort by weight
    uasort($input, '_field_sort_items_helper');

    // Reweight everything in the correct order.
    $weight = -1 * $field_state['items_count'];
    foreach ($input as $key => $item) {
      if ($item) {
        $input[$key]['_weight'] = $weight++;
      }
    }

    drupal_array_set_nested_value($form_state['input'], $address, $input);
    field_form_set_state($parents, $field_name, $langcode, $form_state, $field_state);

    $form_state['rebuild'] = TRUE;
  }

  /**
   *
   */
  function bat_options_element_value_validate($element, &$form_state) {
    $value = $element['#value'];
    if ($value == '') {
      form_set_value($element, 0, $form_state);
    }
  }

}
