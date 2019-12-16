<?php

/**
 * @file
 * Contains \Drupal\bat_options\Plugin\Field\FieldWidget\BatOptionsCombined.
 */

namespace Drupal\bat_options\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Utility\Token;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @FieldWidget(
 *   id = "bat_options_combined",
 *   label = @Translation("Combined text field'"),
 *   field_types = {
 *     "bat_options"
 *   }
 * )
 */
class BatOptionsCombined extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Constructs a BatOptionsCombined object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, Token $token) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('token')
    );
  }

  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    $parents = $form['#parents'];

    $id_prefix = implode('-', array_merge($parents, [$field_name]));
    $wrapper_id = Html::getUniqueId($id_prefix . '-add-more-wrapper');

    $field_state = [];

    // Determine the number of widgets to display.
    switch ($cardinality) {
      case FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED:
        $field_state = static::getWidgetState($parents, $field_name, $form_state);
        $max = $field_state['items_count'];
        $is_multiple = TRUE;
        break;

      default:
        $max = $cardinality - 1;
        $is_multiple = ($cardinality > 1);
        break;
    }

    $title = $this->fieldDefinition->getLabel();
    $description = FieldFilteredMarkup::create($this->token->replace($this->fieldDefinition->getDescription()));

    $elements = [];

    for ($delta = 0; $delta <= $max; $delta++) {
      // Add a new empty item if it doesn't exist yet at this delta.
      if (!isset($items[$delta])) {
        $items->appendItem();
      }

      // For multiple fields, title and description are handled by the wrapping
      // table.
      if ($is_multiple) {
        $element = [
          '#title' => $this->t('@title (value @number)', ['@title' => $title, '@number' => $delta + 1]),
          '#title_display' => 'invisible',
          '#description' => '',
        ];
      }
      else {
        $element = [
          '#title' => $title,
          '#title_display' => 'before',
          '#description' => $description,
        ];
      }

      $element = $this->formSingleElement($items, $delta, $element, $form, $form_state);

      if ($element) {
        // Input field for the delta (drag-n-drop reordering).
        if ($is_multiple) {
          // We name the element '_weight' to avoid clashing with elements
          // defined by widget.
          $element['_weight'] = [
            '#type' => 'weight',
            '#title' => $this->t('Weight for row @number', ['@number' => $delta + 1]),
            '#title_display' => 'invisible',
            // Note: this 'delta' is the FAPI #type 'weight' element's property.
            '#delta' => $max,
            '#default_value' => $items[$delta]->_weight ?: $delta,
            '#weight' => 100,
          ];
        }

        if ($cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED && !$form_state->isProgrammed() && $field_state['items_count'] > 0) {
          $element['remove_item'] = [
            '#type' => 'submit',
            '#value' => $this->t('Remove item'),
            '#submit' => [[get_class($this), 'removeItemSubmit']],
            '#ajax' => [
              'callback' => [get_class($this), 'removeItemAjax'],
              'wrapper' => $wrapper_id,
              'effect' => 'fade',
            ],
            '#name' => implode('_', array_merge($parents, [$field_name, $delta, 'remove_item'])),
            '#attributes' => ['class' => ['field-remove-item-submit']],
            '#limit_validation_errors' => [array_merge($parents, [$field_name])],
            '#weight' => 100,
          ];
        }

        $elements[$delta] = $element;
      }
    }

    if ($elements) {
      $elements += [
        '#theme' => 'field_multiple_value_form',
        '#field_name' => $field_name,
        '#cardinality' => $cardinality,
        '#cardinality_multiple' => $this->fieldDefinition->getFieldStorageDefinition()->isMultiple(),
        '#required' => $this->fieldDefinition->isRequired(),
        '#title' => $title,
        '#description' => $description,
        '#max_delta' => $max,
      ];

      // Add 'add more' button, if not working with a programmed form.
      if ($cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED && !$form_state->isProgrammed()) {
        $elements['#prefix'] = '<div id="' . $wrapper_id . '">';
        $elements['#suffix'] = '</div>';

        $elements['add_more'] = [
          '#type' => 'submit',
          '#name' => strtr($id_prefix, '-', '_') . '_add_more',
          '#value' => t('Add another item'),
          '#attributes' => ['class' => ['field-add-more-submit']],
          '#limit_validation_errors' => [array_merge($parents, [$field_name])],
          '#submit' => [[get_class($this), 'addMoreSubmit']],
          '#ajax' => [
            'callback' => [get_class($this), 'addMoreAjax'],
            'wrapper' => $wrapper_id,
            'effect' => 'fade',
          ],
        ];
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element += [
      '#type' => 'bat_option',
      '#value_callback' => [get_class($this), 'value'],
    ];

    $element['#weight'] = $delta;
    $element['#default_value'] = $items[$delta]->getValue();

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function processMultiple($element, FormStateInterface $form_state, $form) {
    $element['#prefix'] = '<div id="' . $element['#id'] . '-ajax-wrapper">';
    $element['#suffix'] = '</div>';

    return $element;
  }

  public static function removeItemSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    // Go one level up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -1));
    $container_element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -2));
    $field_name = $container_element['#field_name'];
    $field_parents = $element['#field_parents'];
    $delta = $element['#delta'];
    $field_values = &$form_state->getValue($container_element['#parents']);
    $field_input = &NestedArray::getValue($form_state->getUserInput(), $container_element['#parents']);
    $field_state = static::getWidgetState($field_parents, $field_name, $form_state);

    for ($i = $delta; $i < $field_state['items_count']; $i++) {
      $field_values[$i] = $field_values[$i + 1];
      $field_input[$i] = $field_input[$i + 1];
    }
    unset($field_values[$field_state['items_count']]);
    unset($field_input[$field_state['items_count']]);

    // Increment the items count.
    $field_state['items_count']--;
    static::setWidgetState($field_parents, $field_name, $form_state, $field_state);

    $form_state->setRebuild();
  }

  public static function removeItemAjax(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    // Go two levels up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -2));

    return $element;
  }

}
