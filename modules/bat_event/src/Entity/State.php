<?php

namespace Drupal\bat_event\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\bat_event\StateInterface;

/**
 * Defines the State entity.
 *
 * @ContentEntityType(
 *   id = "state",
 *   label = @Translation("State"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\bat_event\StateListBuilder",
 *     "views_data" = "Drupal\bat_event\Entity\StateViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\bat_event\Entity\Form\StateForm",
 *       "add" = "Drupal\bat_event\Entity\Form\StateForm",
 *       "edit" = "Drupal\bat_event\Entity\Form\StateForm",
 *       "delete" = "Drupal\bat_event\Entity\Form\StateDeleteForm",
 *     },
 *     "access" = "Drupal\bat_event\StateAccessControlHandler",
 *   },
 *   base_table = "states",
 *   admin_permission = "administer state entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *   },
 *   links = {
 *     "canonical" = "/admin/bat\state/{state}",
 *     "edit-form" = "/admin/bat\state/{state}/edit",
 *     "delete-form" = "/admin/bat\state/{state}/delete"
 *   }
 * )
 */
class State extends ContentEntityBase implements StateInterface {
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
  public function getMachineName() {
    return $this->get('machine_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getColor() {
    return $this->get('color')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getCalendarLabel() {
    return $this->get('calendar_label')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getBlocking() {
    return $this->get('blocking')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Event entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Event entity.'))
      ->setReadOnly(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Event entity.'))
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
      ->setDescription(t('The name of the State entity.'))
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
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code for the Event entity.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['color'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Color'))
      ->setDescription(t('Color.'));

    $fields['calendar_label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Calendar label'))
      ->setDescription(t('Calendar label.'));

    $fields['blocking'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Blocking'))
      ->setDescription(t('Blocking.'));

    $fields['locked'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Locked'))
      ->setDescription(t('Locked.'))
      ->setDefaultValue('0');

    $fields['machine_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Machine name'))
      ->setDescription(t('Machine name.'));

    $fields['event_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Event type'))
      ->setDescription(t('Event type.'));

    return $fields;
  }

}
