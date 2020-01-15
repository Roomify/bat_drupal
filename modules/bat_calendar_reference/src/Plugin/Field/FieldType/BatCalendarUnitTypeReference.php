<?php

/**
 * @file
 * Contains \Drupal\bat_calendar_reference\Plugin\Field\FieldType\BatCalendarUnitTypeReference.
 */

namespace Drupal\bat_calendar_reference\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceTargetDefinition;
use Drupal\Core\Form\FormStateInterface;

/**
 * @FieldType(
 *   id = "bat_calendar_unit_type_reference",
 *   label = @Translation("BAT Calendar Unit Type Reference"),
 *   description = @Translation("Display unit type events information embedded from other fieldable content."),
 *   default_widget = "bat_calendar_reference_unit_type_autocomplete",
 *   default_formatter = "bat_calendar_reference_timeline_view"
 * )
 */
class BatCalendarUnitTypeReference extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'unit_type_id' => [
          'type'     => 'int',
          'unsigned' => TRUE,
          'not null' => FALSE,
        ],
        'event_type_id' => [
          'type' => 'varchar_ascii',
          'length' => 255,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('unit_type_id')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['unit_type_id'] = DataReferenceTargetDefinition::create('integer')
      ->setLabel(t('Unit type id'))
      ->setSetting('unsigned', TRUE);

    $properties['event_type_id'] = DataReferenceTargetDefinition::create('string')
      ->setLabel(t('Event type id'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'referenceable_event_types' => [],
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $settings = $this->getSettings();

    $element['referenceable_event_types'] = [
      '#type' => 'checkboxes',
      '#title' => t('Event types that can be referenced'),
      '#multiple' => TRUE,
      '#default_value' => $settings['referenceable_event_types'],
      '#options' => array_map('\Drupal\Component\Utility\Html::escape', bat_event_types_ids()),
      '#required' => TRUE,
    ];

    return $element;
  }

}
