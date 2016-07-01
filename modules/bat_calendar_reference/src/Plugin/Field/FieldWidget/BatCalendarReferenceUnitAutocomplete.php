<?php

namespace Drupal\bat_calendar_reference\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @FieldWidget(
 *   id = "bat_calendar_reference_unit_autocomplete",
 *   label = @Translation("Calendar Unit reference"),
 *   field_types = {
 *     "bat_calendar_unit_reference"
 *   }
 * )
 */
class BatCalendarReferenceUnitAutocomplete extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_storage = $this->fieldDefinition->getFieldStorageDefinition();

    $element['unit_id'] = array(
      '#title' => t('Unit'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'bat_unit',
      '#default_value' => isset($items[$delta]->unit_id) ? bat_unit_load($items[$delta]->unit_id) : NULL,
      '#size' => 60,
      '#maxlength' => 255,
      '#validate_reference' => FALSE,
    );

    $element['event_type_id'] = array(
      '#title' => t('Event type'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'bat_event_type',
      '#default_value' => isset($items[$delta]->event_type_id) ? bat_event_type_load($items[$delta]->event_type_id) : NULL,
      '#size' => 60,
      '#maxlength' => 255,
      '#validate_reference' => FALSE,
    );

    return $element;
  }

}
