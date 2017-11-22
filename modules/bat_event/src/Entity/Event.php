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
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\bat_event\EventInterface;
use Drupal\bat_unit\UnitInterface;
use Drupal\user\UserInterface;

use Roomify\Bat\Calendar\Calendar;
use Roomify\Bat\Store\DrupalDBStore;
use Roomify\Bat\Unit\Unit;
use Roomify\Bat\Event\Event as BatEvent;

/**
 * Defines the Event entity.
 *
 * @ingroup bat
 *
 * @ContentEntityType(
 *   id = "bat_event",
 *   label = @Translation("Event"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\bat_event\EventListBuilder",
 *     "views_data" = "Drupal\bat_event\Entity\EventViewsData",
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
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *   },
 *   bundle_entity_type = "bat_event_type",
 *   field_ui_base_route = "entity.bat_event_type.edit_form",
 *   permission_granularity = "bundle",
 *   links = {
 *     "canonical" = "/admin/event/{bat_event}",
 *     "edit-form" = "/admin/event/{bat_event}/edit",
 *     "delete-form" = "/admin/event/{bat_event}/delete"
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
  public function setStartDate(\DateTime $date) {
    $this->set('start', $date->getTimestamp());
  }

  /**
   * {@inheritdoc}
   */
  public function setEndDate(\DateTime $date) {
    $this->set('end', $date->getTimestamp());
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    $event_type = bat_event_type_load($this->bundle());

    // Construct target entity reference field name using this event type's target entity type.
    $target_field_name = 'event_' . $event_type->target_entity_type . '_reference';

    // We are going to be updating the event - so the first step is to remove
    // the old event.
    if (!($this->isNew())) {
      $entity_original = entity_load_unchanged('bat_event', $this->id());

      if (($entity_original->getStartDate() != '') &&
        ($entity_original->getEndDate() != '') &&
        ($entity_original->getTranslation('und')->get($target_field_name) !== FALSE)) {

        // Get the referenced entity ID.
        $event_target_entity_reference = $entity_original->getTranslation('und')->get($target_field_name)->getValue();

        $target_entity_id = 0;
        if (isset($event_target_entity_reference[0]['target_id'])) {
          $target_entity_id = $event_target_entity_reference[0]['target_id'];
        }

        // Load the referenced entity.
        if ($target_entity = entity_load($event_type->target_entity_type, $target_entity_id)) {
          $unit = new Unit($target_entity_id, $target_entity->getEventDefaultValue($event_type->id()));

          $this->batStoreSave($unit,
            $entity_original->getStartDate(),
            $entity_original->getEndDate(),
            $event_type->id(),
            $event_type->getEventGranularity(),
            $unit->getDefaultValue(),
            $this->get('id')->value,
            TRUE
          );
        }
      }
    }

    parent::save();

    // Now we store the new event.
    if ($this->getTranslation('und')->get($target_field_name) !== FALSE) {

      if (isset($event_type->default_event_value_field_ids)) {
        $field = $event_type->default_event_value_field_ids;
        $field_info = FieldStorageConfig::loadByName('bat_event', $field);
        $values = $this->getTranslation('und')->get($field)->getValue();

        if (!empty($values)) {
          if ($field_info->getType() == 'entity_reference') {
            $event_value = $values[0]['target_id'];
          }
          elseif ($field_info->getType() == 'commerce_price') {
            $event_value = $values[0]['amount'];
          }
          elseif ($field_info->getType() == 'text' || $field_info->getType() == 'string' || $field_info->getType() == 'number_integer') {
            $event_value = $values[0]['value'];
          }
        }
      }
      else {
        $event_state_reference = $this->getTranslation('und')->get('event_state_reference')->getValue();
        $event_value = $event_state_reference[0]['target_id'];
      }

      $event_target_entity_reference = $this->getTranslation('und')->get($target_field_name);

      $target_entity_id = $event_target_entity_reference->referencedEntities()[0]->id();

      if ($target_entity = entity_load($event_type->target_entity_type, $target_entity_id)) {
        $unit = new Unit($target_entity_id, $target_entity->getEventDefaultValue($event_type->id()));

        $this->batStoreSave($unit,
          $this->getStartDate(),
          $this->getEndDate(),
          $event_type->id(),
          $event_type->getEventGranularity(),
          $event_value,
          $this->get('id')->value
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

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Event entity.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
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
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setRequired(TRUE);

    $fields['end'] = BaseFieldDefinition::create('created')
      ->setLabel(t('End Date'))
      ->setDescription(t('The time that this event ends.'))
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setRequired(TRUE);

    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The event type.'))
      ->setSetting('target_type', 'bat_event_type');

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

    $units = [$unit];
    $state_calendar = new Calendar($units, $state_store);
    $event_calendar = new Calendar($units, $event_store);

    $state_event = new BatEvent($start_date, $end_date, $unit, $event_state);
    if (!$remove) {
      $event_id_event = new BatEvent($start_date, $end_date, $unit, $event_id);
    }
    else {
      $event_id_event = new BatEvent($start_date, $end_date, $unit, 0);
    }

    $state_calendar->addEvents([$state_event], $granularity);
    $event_calendar->addEvents([$event_id_event], $granularity);
  }

  public function getEventValue() {
    if ($field = $this->getEventValueField()) {
      $field_info = FieldStorageConfig::loadByName('bat_event', $field);
      $values = $this->getTranslation('und')->get($field)->getValue();

      if (!empty($values)) {
        if ($field_info->getType() == 'entity_reference') {
          return $values[0]['target_id'];
        }
        elseif ($field_info->getType() == 'commerce_price') {
          return $values[0]['amount'];
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
   * Returns the formatter that can format the event value
   *
   * @return string|FALSE
   */
  public function getEventValueFormatter() {
    if ($field = $this->getEventValueDefaultField()) {
      $field_info_instance = field_info_instance('bat_event_type', $field, $this->type);

      if (isset($field_info_instance['display']['default']['type'])) {
        return $field_info_instance['display']['default']['type'];
      }
    }

    return FALSE;
  }

  /**
   * Determines which field holds the event value
   *
   * @return string|FALSE
   */
  public function getEventValueField() {
    $type_bundle = bat_event_type_load($this->bundle());

    if (isset($type_bundle->default_event_value_field_ids)) {
      return $type_bundle->default_event_value_field_ids;
    }

    if ($type_bundle->getFixedEventStates() == 1) {
      return 'event_state_reference';
    }

    return FALSE;
  }

}
