<?php

namespace Drupal\bat_calendar_reference\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @FieldWidget(
 *   id = "bat_calendar_reference_unit_type_autocomplete",
 *   label = @Translation("Calendar Unit type reference"),
 *   field_types = {
 *     "bat_calendar_unit_type_reference"
 *   }
 * )
 */
class BatCalendarReferenceUnitTypeAutocomplete extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $referenceable_event_types = array_filter($this->fieldDefinition->getSetting('referenceable_event_types'));

    $element['unit_type_id'] = [
      '#title' => t('Unit type'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'bat_unit_type',
      '#default_value' => isset($items[$delta]->unit_type_id) ? bat_type_load($items[$delta]->unit_type_id) : NULL,
      '#size' => 60,
      '#maxlength' => 255,
      '#validate_reference' => FALSE,
    ];

    $element['event_type_id'] = [
      '#title' => t('Event type'),
      '#type' => 'bat_event_type_autocomplete',
      '#target_type' => 'bat_event_type',
      '#default_value' => isset($items[$delta]->event_type_id) ? bat_event_type_load($items[$delta]->event_type_id) : NULL,
      '#selection_settings' => ['event_types' => $referenceable_event_types],
      '#size' => 60,
      '#maxlength' => 255,
      '#validate_reference' => FALSE,
    ];

    return $element;
  }

}
