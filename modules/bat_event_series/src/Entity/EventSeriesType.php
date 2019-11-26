<?php

/**
 * @file
 * Contains \Drupal\bat_event_series\Entity\EventSeriesType.
 */

namespace Drupal\bat_event_series\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\bat_event\Entity\EventType;
use Drupal\bat_event_series\EventSeriesTypeInterface;

/**
 * Defines the Event series type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "bat_event_series_type",
 *   label = @Translation("Event series type"),
 *   handlers = {
 *     "access" = "Drupal\bat_event_series\EventSeriesTypeAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\bat_event_series\EventSeriesTypeForm",
 *       "edit" = "Drupal\bat_event_series\EventSeriesTypeForm",
 *       "delete" = "Drupal\bat_event_series\Form\EventSeriesTypeDeleteConfirm"
 *     },
 *     "list_builder" = "Drupal\bat_event_series\EventSeriesTypeListBuilder",
 *   },
 *   admin_permission = "administer event_series_type entities",
 *   config_prefix = "event_series_type",
 *   bundle_of = "bat_event_series",
 *   entity_keys = {
 *     "id" = "type",
 *     "label" = "name"
 *   },
 *   links = {
 *     "edit-form" = "/admin/bat/event/event-types/manage/{bat_event_series_type}",
 *     "delete-form" = "/admin/bat/event/event-types/manage/{bat_event_series_type}/delete",
 *     "collection" = "/admin/bat/event/event-types",
 *   },
 *   config_export = {
 *     "name",
 *     "type",
 *     "event_granularity",
 *     "target_event_type",
 *   }
 * )
 */
class EventSeriesType extends ConfigEntityBundleBase implements EventSeriesTypeInterface {

  /**
   * The machine name of this event series type.
   *
   * @var string
   */
  protected $type;

  /**
   * The human-readable name of the event series type.
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
   * The target event type.
   *
   * @var string
   */
  protected $target_event_type;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->type;
  }

  public function getEventGranularity() {
    return $this->event_granularity;
  }

  public function getTargetEventType() {
    return $this->target_event_type;
  }

  public function getTargetEntityType() {
    $event_type = EventType::load($this->target_event_type);

    return $event_type->getTargetEntityType();
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    $is_new = $this->isNew();

    parent::save();

    if ($is_new) {
      // Create a field of type "Date range" for event dates.
      bat_event_series_type_add_event_dates_field($this->id());

      // Create a field of type 'Entity Reference' to reference a Bat Unit.
      bat_event_series_type_add_target_entity_field($this->id(), $this->getTargetEntityType());

      // Create a field of type 'Bat Event State Reference' to reference an Event State.
      bat_event_series_type_add_event_state_reference($this->id());
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

}
