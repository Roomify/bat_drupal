<?php

/**
 * @file
 * Contains \Drupal\bat_unit\Entity\Unit.
 */

namespace Drupal\bat_unit\Entity;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\bat_unit\UnitInterface;
use Drupal\bat_unit\UnitTypeInterface;
use Drupal\user\UserInterface;
use Drupal\commerce_price\Price;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the Unit entity.
 *
 * @ingroup bat
 *
 * @ContentEntityType(
 *   id = "bat_unit",
 *   label = @Translation("Unit"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\bat_unit\UnitListBuilder",
 *     "views_data" = "Drupal\bat_unit\Entity\UnitViewsData",
 *     "form" = {
 *       "default" = "Drupal\bat_unit\Entity\Form\UnitForm",
 *       "add" = "Drupal\bat_unit\Entity\Form\UnitForm",
 *       "edit" = "Drupal\bat_unit\Entity\Form\UnitForm",
 *       "delete" = "Drupal\bat_unit\Entity\Form\UnitDeleteForm",
 *     },
 *     "access" = "Drupal\bat_unit\UnitAccessControlHandler",
 *   },
 *   base_table = "unit",
 *   admin_permission = "administer unit entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *     "owner" = "uid",
 *     "langcode" = "langcode",
 *   },
 *   bundle_entity_type = "bat_unit_bundle",
 *   field_ui_base_route = "entity.bat_unit_bundle.edit_form",
 *   permission_granularity = "bundle",
 *   links = {
 *     "canonical" = "/admin/unit/{bat_unit}",
 *     "edit-form" = "/admin/unit/{bat_unit}/edit",
 *     "delete-form" = "/admin/unit/{bat_unit}/delete"
 *   }
 * )
 */
class Unit extends ContentEntityBase implements UnitInterface {
  use EntityChangedTrait, EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'uid' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUnitType() {
    return $this->get('unit_type_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getUnitTypeId() {
    return $this->get('unit_type_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setUnitTypeId($utid) {
    $this->set('unit_type_id', $utid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setUnitType(UnitTypeInterface $unit_type) {
    $this->set('unit_type_id', $unit_type->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus($status) {
    $this->set('status', $status);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Unit entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Unit entity.'))
      ->setReadOnly(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Unit entity.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback(static::class . '::getCurrentUserId')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['unit_type_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Unit Type'))
      ->setDescription(t('The ID of the Unit Type entity this Unit entity belongs to.'))
      ->setSetting('target_type', 'bat_unit_type')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_label',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Unit entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the entity was created.'))
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The unit bundle.'))
      ->setSetting('target_type', 'bat_unit_bundle');

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Published'))
      ->setDefaultValue(TRUE);

    return $fields;
  }

  /**
   * @param $event_type
   *
   * @return
   */
  public function getEventDefaultValue($event_type) {
    $unit_type = $this->getUnitType();

    return $unit_type->getEventDefaultValue($event_type);
  }

  /**
   * @param $event_type
   * @param $value
   *
   * @return mixed
   */
  public function formatEventValue($event_type, $value) {
    $bat_type = $this->unit_type_id->entity;

    $field = $bat_type->getEventValueDefaultField($event_type);
    $field_info = FieldStorageConfig::loadByName('bat_unit_type', $field);
    $field_info_instance = FieldConfig::loadByName('bat_unit_type', $bat_type->bundle(), $field);

    $temp_bat_type = clone($bat_type);

    if ($field_info->getType() == 'commerce_price') {
      $currency_code = 'USD';

      if ($default_value = $field_info_instance->get('default_value') && !empty($default_value)) {
        $currency_code = $default_value[0]['currency_code'];
      }
      elseif ($available_currencies = $field_info_instance->getSetting('available_currencies')) {
        $available_currencies = array_filter($available_currencies);
        $currency_code = reset($available_currencies);
      }

      $price = new Price($value, $currency_code);

      $temp_bat_type->set($field, $price);

      $elements = $temp_bat_type->{$field}->view(['label' => 'hidden']);
      $field_view_value = \Drupal::service('renderer')->renderPlain($elements);
    }
    else {
      $temp_bat_type->set($field, $value);

      $elements = $temp_bat_type->{$field}->view(['label' => 'hidden']);
      $field_view_value = \Drupal::service('renderer')->renderPlain($elements);
    }

    return trim(strip_tags($field_view_value->__toString()));
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

}
