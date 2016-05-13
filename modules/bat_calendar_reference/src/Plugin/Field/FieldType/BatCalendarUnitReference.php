<?php

namespace Drupal\bat_calendar_reference\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceTargetDefinition;

/**
 * @FieldType(
 *   id = "bat_calendar_unit_reference",
 *   label = @Translation("BAT Calendar Unit Reference"),
 *   description = @Translation("Display unit events information embedded from other fieldable content."),
 *   default_widget = "bat_calendar_reference_unit_autocomplete",
 *   default_formatter = "bat_calendar_reference_timeline_view"
 * )
 */
class BatCalendarUnitReference extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field) {
    return array(
      'columns' => array(
        'unit_id' => array(
          'type'     => 'int',
          'unsigned' => TRUE,
          'not null' => FALSE,
        ),
        'event_type_id' => array(
          'type'     => 'int',
          'unsigned' => TRUE,
          'not null' => FALSE,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('unit_id')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['unit_id'] = DataReferenceTargetDefinition::create('integer')
      ->setLabel(t('Uni id'))
      ->setSetting('unsigned', TRUE);

    $properties['event_type_id'] = DataReferenceTargetDefinition::create('integer')
      ->setLabel(t('Event type id'))
      ->setSetting('unsigned', TRUE);

    return $properties;
  }

}
