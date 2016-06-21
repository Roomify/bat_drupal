<?php

/**
 * @file
 * Contains \Drupal\bat_unit\Entity\UnitType.
 */

namespace Drupal\bat_unit\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\bat_unit\UnitTypeInterface;
use Drupal\user\UserInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Defines the Unit type entity.
 *
 * @ingroup bat
 *
 * @ContentEntityType(
 *   id = "bat_unit_type",
 *   label = @Translation("Unit type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\bat_unit\UnitTypeListBuilder",
 *     "views_data" = "Drupal\bat_unit\Entity\UnitTypeViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\bat_unit\Entity\Form\UnitTypeForm",
 *       "add" = "Drupal\bat_unit\Entity\Form\UnitTypeForm",
 *       "edit" = "Drupal\bat_unit\Entity\Form\UnitTypeForm",
 *       "delete" = "Drupal\bat_unit\Entity\Form\UnitTypeDeleteForm",
 *     },
 *     "access" = "Drupal\bat_unit\UnitTypeAccessControlHandler",
 *   },
 *   base_table = "unit_type",
 *   admin_permission = "administer UnitType entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *   },
 *   bundle_entity_type = "type_bundle",
 *   field_ui_base_route = "entity.type_bundle.edit_form",
 *   links = {
 *     "canonical" = "/admin/unit_type/{bat_unit_type}",
 *     "edit-form" = "/admin/unit_type/{bat_unit_type}/edit",
 *     "delete-form" = "/admin/unit_type/{bat_unit_type}/delete"
 *   },
 * )
 */
class UnitType extends ContentEntityBase implements UnitTypeInterface {
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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Unit type entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Unit type entity.'))
      ->setReadOnly(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Unit type entity.'))
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

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Unit type entity.'))
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
      ->setDescription(t('The language code for the Unit type entity.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The type bundle.'))
      ->setSetting('target_type', 'type_bundle');

    return $fields;
  }

  /**
   *
   */
  public function getEventDefaultValue($event_type) {
    if ($field = $this->getEventValueDefaultField($event_type)) {
      $field_info = FieldStorageConfig::loadByName('bat_unit_type', $field);
      $values = $this->getTranslation('und')->get($field)->getValue();

      if (!empty($values)) {
        if ($field_info->getType() == 'entity_reference') {
          return $values[0]['target_id'];
        }
        elseif ($field_info->getType() == 'commerce_price') {
          return $values[0]['amount'];
        }
        elseif ($field_info->getType() == 'text' || $field_info->getType() == 'number_integer') {
          return $values[0]['value'];
        }
      }
      else {
        return FALSE;
      }
    }
  }

  /**
   * @param $event_type
   *
   * @return string|FALSE
   */
  public function getEventValueFormatter($event_type) {
    if ($field = $this->getEventValueDefaultField($event_type)) {
      $field_info_instance = FieldConfig::loadByName('bat_unit_type', $field, $this->type);

      if (isset($field_info_instance['display']['default']['type'])) {
        return $field_info_instance['display']['default']['type'];
      }
    }

    return FALSE;
  }

  /**
   * @param $event_type
   *
   * @return string|FALSE
   */
  public function getEventValueDefaultField($event_type) {
    $type_bundle = bat_type_bundle_load($this->bundle());

    if (isset($type_bundle->default_event_value_field_ids[$event_type])) {
      return $type_bundle->default_event_value_field_ids[$event_type];
    }

    return FALSE;
  }

}
