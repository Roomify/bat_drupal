<?php

/**
 * @file
 * Contains \Drupal\bat_event\Entity\Event.
 */

namespace Drupal\bat_event\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Database\Database;
use Drupal\bat_event\EventInterface;
use Drupal\bat_event\StateInterface;
use Drupal\bat_unit\UnitInterface;
use Drupal\user\UserInterface;

use Roomify\Bat\Calendar\Calendar;
use Roomify\Bat\Store\DrupalDBStore;
use Roomify\Bat\Unit\Unit;

/**
 * Defines the Event entity.
 *
 * @ingroup bat
 *
 * @ContentEntityType(
 *   id = "event",
 *   label = @Translation("Event"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\bat_event\EventListBuilder",
 *     "views_data" = "Drupal\bat_event\Entity\EventViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\bat_event\Entity\Form\EventForm",
 *       "add" = "Drupal\bat_event\Entity\Form\EventForm",
 *       "edit" = "Drupal\bat_event\Entity\Form\EventForm",
 *       "delete" = "Drupal\bat_event\Entity\Form\EventDeleteForm",
 *     },
 *     "access" = "Drupal\bat_event\EventAccessControlHandler",
 *   },
 *   base_table = "event",
 *   admin_permission = "administer event entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *   },
 *   bundle_entity_type = "event_type",
 *   field_ui_base_route = "entity.event_type.edit_form",
 *   links = {
 *     "canonical" = "/admin/event/{event}",
 *     "edit-form" = "/admin/event/{event}/edit",
 *     "delete-form" = "/admin/event/{event}/delete"
 *   }
 * )
 */
class Event extends ContentEntityBase implements EventInterface {
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
  public function getUnit() {
    return $this->get('unit_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getUnitId() {
    return $this->get('unit_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setUnitId($unit_id) {
    $this->set('unit_id', $unit_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setUnit(UnitInterface $unit) {
    $this->set('unit_id', $unit->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getState() {
    return $this->get('state_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getStateId() {
    return $this->get('state_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setStateId($state_id) {
    $this->set('state_id', $state_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setState(StateInterface $state) {
    $this->set('state_id', $state->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStartDate() {
    $date = new \DateTime();
    return $date->setTimestamp($this->get('start')->value);
  }

  /**
   * {@inheritdoc}
   */
  public function getEndDate() {
    $date = new \DateTime();
    return $date->setTimestamp($this->get('end')->value);
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    $entity->original = entity_load_unchanged($this->entityType, $entity->{$this->idKey});

    $event_type = bat_event_type_load($entity->type);

    // Construct target entity reference field name using this event type's target entity type.
    $target_field_name = 'event_' . $event_type->target_entity_type . '_reference';

    // We are going to be updating the event - so the first step is to remove
    // the old event.
    if (!($this->isNew()) &&
        ($entity->original->start_date != '') &&
        ($entity->original->end_date != '') &&
        (field_get_items('bat_event', $entity->original, $target_field_name) !== FALSE)) {

      // Get the referenced entity ID.
      $event_target_entity_reference = field_get_items('bat_event', $entity->original, $target_field_name);
      $target_entity_id = $event_target_entity_reference[0]['target_id'];
      // Load the referenced entity.
      if ($target_entity = entity_load_single($event_type->target_entity_type, $target_entity_id)) {
        $unit = new Unit($target_entity_id, $target_entity->getEventDefaultValue($event_type->type));

        $this->batStoreSave($unit,
          new \DateTime($entity->original->start_date),
          new \DateTime($entity->original->end_date),
          $event_type->type,
          $event_type->event_granularity,
          $unit->getDefaultValue(),
          $entity->event_id,
          TRUE
        );
      }
    }

    parent::save();

    // Now we store the new event.
    if (field_get_items('bat_event', $entity, $target_field_name) !== FALSE) {

      if (isset($event_type->default_event_value_field_ids[$entity->type])) {
        $field = $event_type->default_event_value_field_ids[$entity->type];
        $field_info = field_info_field($field);
        $values = field_get_items('bat_event', $entity, $field);

        if (!empty($values)) {
          if ($field_info['type'] == 'bat_event_state_reference') {
            $event_value = $values[0]['state_id'];
          }
          elseif ($field_info['type'] == 'commerce_price') {
            $event_value = $values[0]['amount'];
          }
          elseif ($field_info['type'] == 'text' || $field_info['type'] == 'number_integer') {
            $event_value = $values[0]['value'];
          }
        }
      }
      else {
        $event_state_reference = field_get_items('bat_event', $entity, 'event_state_reference');
        $event_value = $event_state_reference[0]['state_id'];
      }

      $event_target_entity_reference = field_get_items('bat_event', $entity, $target_field_name);
      $target_entity_id = $event_target_entity_reference[0]['target_id'];
      if ($target_entity = entity_load_single($event_type->target_entity_type, $target_entity_id)) {
        $unit = new Unit($target_entity_id, $target_entity->getEventDefaultValue($event_type->type));

        $this->batStoreSave($unit,
          new \DateTime($entity->start_date),
          new \DateTime($entity->end_date),
          $event_type->type,
          $event_type->event_granularity,
          $event_value,
          $entity->event_id
        );
      }
    }
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
        'weight' => 3,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code for the Event entity.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['start'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Start Date'))
      ->setDescription(t('The time that this event starts.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'datetime_timestamp',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['end'] = BaseFieldDefinition::create('created')
      ->setLabel(t('End Date'))
      ->setDescription(t('The time that this event ends.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'datetime_timestamp',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['unit_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Unit'))
      ->setDescription(t('The ID of the Unit entity this Event entity is associated with.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'unit')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'property',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 1,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['state_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('State'))
      ->setDescription(t('The ID of the State entity this Event entity is associated with.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'state')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'property',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 2,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

  /**
   * Handles saving to the BatStore
   *
   * @param \Roomify\Bat\Unit\Unit $unit - The unit to save
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param $event_type
   * @param $granularity
   * @param $event_state
   * @param $event_id
   * @param bool|FALSE $remove - set to TRUE if the event is to be removed (event_id set to zero)
   */
  public function batStoreSave(Unit $unit, \DateTime $start_date, \DateTime $end_date, $event_type, $granularity, $event_state, $event_id, $remove = FALSE) {
    $database = Database::getConnectionInfo('default');

    $prefix = (isset($database['default']['prefix']['default'])) ? $database['default']['prefix']['default'] : '';

    $state_store = new DrupalDBStore($event_type, DrupalDBStore::BAT_STATE, $prefix);
    $event_store = new DrupalDBStore($event_type, DrupalDBStore::BAT_EVENT, $prefix);

    $units = array($unit);
    $state_calendar = new Calendar($units, $state_store);
    $event_calendar = new Calendar($units, $event_store);

    $state_event = new \Roomify\Bat\Event\Event($start_date, $end_date, $unit, $event_state);
    if (!$remove) {
      $event_id_event = new \Roomify\Bat\Event\Event($start_date, $end_date, $unit, $event_id);
    }
    else {
      $event_id_event = new \Roomify\Bat\Event\Event($start_date, $end_date, $unit, 0);
    }

    $state_calendar->addEvents(array($state_event), $granularity);
    $event_calendar->addEvents(array($event_id_event), $granularity);
  }

}
