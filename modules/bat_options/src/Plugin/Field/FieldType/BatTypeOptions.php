<?php

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

  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {

  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    return $properties;
  }

}
