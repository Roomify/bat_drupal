<?php

/**
 * @file
 * Contains \Drupal\bat_event\Entity\EventType.
 */

namespace Drupal\bat_event\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\bat_event\EventTypeInterface;

/**
 * Defines the Event type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "bat_event_type",
 *   label = @Translation("Event type"),
 *   handlers = {
 *     "access" = "Drupal\bat_event\EventTypeAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\bat_event\EventTypeForm",
 *       "edit" = "Drupal\bat_event\EventTypeForm",
 *       "delete" = "Drupal\bat_event\Form\EventTypeDeleteConfirm"
 *     },
 *     "list_builder" = "Drupal\bat_event\EventTypeListBuilder",
 *   },
 *   admin_permission = "administer event_type entities",
 *   config_prefix = "event_type",
 *   bundle_of = "bat_event",
 *   entity_keys = {
 *     "id" = "type",
 *     "label" = "name"
 *   },
 *   links = {
 *     "edit-form" = "/admin/bat/event/event-types/manage/{bat_event_type}",
 *     "delete-form" = "/admin/bat/event/event-types/manage/{bat_event_type}/delete",
 *     "collection" = "/admin/bat/event/event-types",
 *   },
 *   config_export = {
 *     "name",
 *     "type",
 *     "event_granularity",
 *     "fixed_event_states",
 *     "default_event_value_field_ids",
 *     "default_event_label_field_name",
 *     "target_entity_type",
 *   }
 * )
 */
class EventType extends ConfigEntityBundleBase implements EventTypeInterface {

  /**
   * The machine name of this event type.
   *
   * @var string
   */
  protected $type;

  /**
   * The human-readable name of the event type.
   *
   * @var string
   */
  protected $name;

  /**
   * The event granularity.
   *
   * @var string
   */
  protected $event_granularity;

  /**
   * Whether event states are fixed or open.
   *
   * @var bool
   */
  protected $fixed_event_states;

  /**
   * The target entity type.
   *
   * @var string
   */
  protected $target_entity_type;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->type;
  }

  public function getEventGranularity() {
    return $this->event_granularity;
  }

  public function getFixedEventStates() {
    return $this->fixed_event_states;
  }

  public function getTargetEntityType() {
    return $this->target_entity_type;
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    $is_new = $this->isNew();

    parent::save();

    if ($is_new) {
      // Create all tables necessary for this Event Type.
      bat_event_create_event_type_schema($this->id());

      // Create a field of type "Date range" for event dates.
      bat_event_type_add_event_dates_field($this);

      // Create a field of type 'Entity Reference' to reference a Bat Unit.
      bat_event_type_add_target_entity_field($this);

      if ($this->fixed_event_states) {
        // Create a field of type 'Bat Event State Reference' to reference an Event State.
        bat_event_type_add_event_state_reference($this);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    // Delete all tables necessary for this Event Type.
    bat_event_delete_event_type_schema($this->id());

    parent::delete();
  }

}
