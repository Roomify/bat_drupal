<?php

/**
 * @file
 * Contains \Drupal\bat_options\Plugin\Field\FieldType\BatTypeOptions.
 */

namespace Drupal\bat_options\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceTargetDefinition;

/**
 * @FieldType(
 *   id = "bat_options",
 *   label = @Translation("BAT Type Options"),
 *   description = @Translation("BAT Type Options."),
 *   default_widget = "bat_options_combined",
 *   default_formatter = "bat_options_default"
 * )
 */
class BatTypeOptions extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field) {
    return [
      'columns' => [
        'name' => [
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
        ],
        'quantity' => [
          'type' => 'int',
          'not null' => FALSE,
        ],
        'operation' => [
          'type' => 'varchar',
          'length' => 255,
          'not null' => FALSE,
        ],
        'value' => [
          'type' => 'float',
          'not null' => FALSE,
        ],
        'type' => [
          'type' => 'varchar',
          'length' => 255,
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->get('name')->getValue()) ||
         empty($this->get('quantity')->getValue()) ||
         !(is_numeric($this->get('quantity')->getValue()) && is_int((int) $this->get('quantity')->getValue())) ||
         ((empty($this->get('value')->getValue()) || !is_numeric($this->get('value')->getValue())) && $this->get('operation')->getValue() != 'no_charge') ||
         empty($this->get('operation')->getValue()) || !in_array($this->get('operation')->getValue(), array_keys(bat_options_price_options()));
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['name'] = DataReferenceTargetDefinition::create('string')
      ->setLabel(t('Name'));

    $properties['quantity'] = DataReferenceTargetDefinition::create('integer')
      ->setLabel(t('Quantity'));

    $properties['operation'] = DataReferenceTargetDefinition::create('string')
      ->setLabel(t('Operation'));

    $properties['value'] = DataReferenceTargetDefinition::create('float')
      ->setLabel(t('Value'));

    $properties['type'] = DataReferenceTargetDefinition::create('string')
      ->setLabel(t('Type'));

    return $properties;
  }

}
