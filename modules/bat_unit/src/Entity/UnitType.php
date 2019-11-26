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
use Drupal\user\EntityOwnerTrait;

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
 *     "form" = {
 *       "default" = "Drupal\bat_unit\Entity\Form\UnitTypeForm",
 *       "add" = "Drupal\bat_unit\Entity\Form\UnitTypeForm",
 *       "edit" = "Drupal\bat_unit\Entity\Form\UnitTypeForm",
 *       "delete" = "Drupal\bat_unit\Entity\Form\UnitTypeDeleteForm",
 *     },
 *     "access" = "Drupal\bat_unit\UnitTypeAccessControlHandler",
 *   },
 *   base_table = "unit_type",
 *   admin_permission = "administer unit_type entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *     "owner" = "uid",
 *     "langcode" = "langcode",
 *   },
 *   bundle_entity_type = "bat_type_bundle",
 *   field_ui_base_route = "entity.bat_type_bundle.edit_form",
 *   permission_granularity = "bundle",
 *   links = {
 *     "canonical" = "/admin/unit_type/{bat_unit_type}",
 *     "edit-form" = "/admin/unit_type/{bat_unit_type}/edit",
 *     "delete-form" = "/admin/unit_type/{bat_unit_type}/delete"
 *   },
 * )
 */
class UnitType extends ContentEntityBase implements UnitTypeInterface {
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
  public function getStatus() {
    return $this->get('status')->value;
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
      ->setDescription(t('The ID of the Unit type entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Unit type entity.'))
      ->setReadOnly(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Unit type entity.'))
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

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Unit type entity.'))
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
      ->setDescription(t('The type bundle.'))
      ->setSetting('target_type', 'bat_type_bundle');

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Published'))
      ->setDefaultValue(TRUE);

    $fields['group_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Group'))
      ->setDescription(t('The type group.'))
      ->setSetting('target_type', 'bat_type_group')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ]);

    return $fields;
  }

  /**
   * @param $event_type
   */
  public function getEventDefaultValue($event_type) {
    $langcode = $this->defaultLangcode;

    if ($field = $this->getEventValueDefaultField($event_type)) {
      $field_info = FieldStorageConfig::loadByName('bat_unit_type', $field);
      $values = $this->getTranslation($langcode)->get($field)->getValue();

      if (!empty($values)) {
        if ($field_info->getType() == 'entity_reference') {
          return $values[0]['target_id'];
        }
        elseif ($field_info->getType() == 'commerce_price') {
          return $values[0]['number'];
        }
        elseif ($field_info->getType() == 'text' || $field_info->getType() == 'string' || $field_info->getType() == 'number_integer') {
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
      $field_info_instance = FieldConfig::loadByName('bat_unit_type', $this->bundle(), $field);

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
