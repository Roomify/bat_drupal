<?php

/**
 * @file
 * Contains \Drupal\bat_unit\Entity\TypeBundle.
 */

namespace Drupal\bat_unit\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\bat_unit\TypeBundleInterface;

/**
 * Defines the Type Bundle configuration entity.
 *
 * @ConfigEntityType(
 *   id = "bat_type_bundle",
 *   label = @Translation("Type bundle"),
 *   handlers = {
 *     "access" = "Drupal\bat_unit\TypeBundleAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\bat_unit\TypeBundleForm",
 *       "edit" = "Drupal\bat_unit\TypeBundleForm",
 *       "delete" = "Drupal\bat_unit\Form\TypeBundleDeleteConfirm"
 *     },
 *     "list_builder" = "Drupal\bat_unit\TypeBundleListBuilder",
 *   },
 *   admin_permission = "administer bat_type_bundle entities",
 *   config_prefix = "type_bundle",
 *   bundle_of = "bat_unit_type",
 *   entity_keys = {
 *     "id" = "type",
 *     "label" = "name"
 *   },
 *   links = {
 *     "edit-form" = "/admin/bat/type-bundles/manage/{bat_type_bundle}",
 *     "delete-form" = "/admin/bat/type-bundles/manage/{bat_type_bundle}/delete",
 *     "collection" = "/admin/bat/type-bundles",
 *   },
 *   config_export = {
 *     "name",
 *     "type",
 *     "default_event_value_field_ids",
 *   }
 * )
 */
class TypeBundle extends ConfigEntityBundleBase implements TypeBundleInterface {

  /**
   * The machine name of this event type.
   *
   * @var string
   *
   * @todo Rename to $id.
   */
  protected $type;

  /**
   * The human-readable name of the event type.
   *
   * @var string
   *
   * @todo Rename to $label.
   */
  protected $name;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function getFixedEventStates() {
    return $this->get('fixed_event_states')->value;
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
