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
      '#title' => t('Unit type'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'unit_type',
      //'#selection_settings' => ['target_bundles' => $target_bundles],
      '#default_value' => isset($items[$delta]->unit_type_id) ? $items[$delta]->unit_type_id : NULL,
      '#size' => 60,
      '#maxlength' => 255,
      '#validate_reference' => FALSE,
    );

    $element['event_type_id'] = array(
      '#title' => t('Event type'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'event_type',
      //'#selection_settings' => ['target_bundles' => $target_bundles],
      '#default_value' => isset($items[$delta]->event_type_id) ? $items[$delta]->event_type_id : NULL,
      '#size' => 60,
      '#maxlength' => 255,
      '#validate_reference' => FALSE,
    );

    return $element;
  }

}
