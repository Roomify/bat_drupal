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
    $field_storage = $this->fieldDefinition->getFieldStorageDefinition();

    $element['unit_type_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Unit type'),
      '#default_value' => isset($items[$delta]->unit_type_id) ? $items[$delta]->unit_type_id : NULL,
      '#autocomplete_route_name' => 'bat_calendar_reference.unit_type_autocomplete',
      '#autocomplete_route_parameters' => array('entity_type' => $field_storage->getTargetEntityTypeId(), 'bundle' => $this->fieldDefinition->getTargetBundle(), 'field_name' => $field_storage->getName()),
      '#size' => 60,
      '#maxlength' => 255,
      '#element_validate' => array('bat_calendar_reference_autocomplete_unit_type_validate'),
      '#value_callback' => 'bat_calendar_reference_unit_type_autocomplete_value',
    );

    $element['event_type_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Event type'),
      '#default_value' => isset($items[$delta]->event_type_id) ? $items[$delta]->event_type_id : NULL,
      '#autocomplete_route_name' => 'bat_calendar_reference.event_type_autocomplete',
      '#autocomplete_route_parameters' => array('entity_type' => $field_storage->getTargetEntityTypeId(), 'bundle' => $this->fieldDefinition->getTargetBundle(), 'field_name' => $field_storage->getName()),
      '#size' => 60,
      '#maxlength' => 255,
      '#element_validate' => array('bat_calendar_reference_autocomplete_event_type_validate'),
      '#value_callback' => 'bat_calendar_reference_event_type_autocomplete_value',
    );

    return $element;
  }

}
