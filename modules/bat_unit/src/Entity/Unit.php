<?php

/**
 * @file
 * Contains \Drupal\bat_unit\Entity\Unit.
 */

namespace Drupal\bat_unit\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\bat_unit\UnitInterface;
use Drupal\bat\PropertyInterface;
use Drupal\bat_unit\UnitTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Unit entity.
 *
 * @ingroup bat
 *
 * @ContentEntityType(
 *   id = "unit",
 *   label = @Translation("Unit"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\bat_unit\UnitListBuilder",
 *     "views_data" = "Drupal\bat_unit\Entity\UnitViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\bat_unit\Entity\Form\UnitForm",
 *       "add" = "Drupal\bat_unit\Entity\Form\UnitForm",
 *       "edit" = "Drupal\bat_unit\Entity\Form\UnitForm",
 *       "delete" = "Drupal\bat_unit\Entity\Form\UnitDeleteForm",
 *     },
 *     "access" = "Drupal\bat_unit\UnitAccessControlHandler",
 *   },
 *   base_table = "unit",
 *   admin_permission = "administer Unit entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *   },
 *   bundle_entity_type = "unit_bundle",
 *   field_ui_base_route = "entity.unit_bundle.edit_form",
 *   links = {
 *     "canonical" = "/admin/unit/{unit}",
 *     "edit-form" = "/admin/unit/{unit}/edit",
 *     "delete-form" = "/admin/unit/{unit}/delete"
 *   }
 * )
 */
class Unit extends ContentEntityBase implements UnitInterface {
  use EntityChangedTrait;
  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
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
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getProperty() {
    return $this->get('property_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyId() {
    return $this->get('property_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setPropertyId($pid) {
    $this->set('property_id', $pid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setProperty(PropertyInterface $property) {
    $this->set('property_id', $property->id());
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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Unit entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Unit entity.'))
      ->setReadOnly(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Unit entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['unit_type_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Unit Type'))
      ->setDescription(t('The ID of the Unit Type entity this Unit entity belongs to.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'unit_type')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'property',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Unit entity.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code for the Unit entity.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The unit bundle.'))
      ->setSetting('target_type', 'unit_bundle');

    return $fields;
  }

  /**
   *
   */
  public function getEventDefaultValue($event_type) {
    $unit_type = $this->getUnitType();

    return $unit_type->getEventDefaultValue($event_type);
  }

}
