<?php

/**
 * @file
 * Contains \Drupal\bat_unit\Entity\UnitBundle.
 */

namespace Drupal\bat_unit\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\bat_unit\UnitBundleInterface;

/**
 * Defines the Unit Bundle configuration entity.
 *
 * @ConfigEntityType(
 *   id = "bat_unit_bundle",
 *   label = @Translation("Unit bundle"),
 *   handlers = {
 *     "access" = "Drupal\bat_unit\UnitBundleAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\bat_unit\UnitBundleForm",
 *       "edit" = "Drupal\bat_unit\UnitBundleForm",
 *       "delete" = "Drupal\bat_unit\Form\UnitBundleDeleteConfirm"
 *     },
 *     "list_builder" = "Drupal\bat_unit\UnitBundleListBuilder",
 *   },
 *   admin_permission = "administer unit_bundle entities",
 *   config_prefix = "unit_bundle",
 *   bundle_of = "bat_unit",
 *   entity_keys = {
 *     "id" = "type",
 *     "label" = "name"
 *   },
 *   links = {
 *     "edit-form" = "/admin/bat/unit-bundles/manage/{bat_unit_bundle}",
 *     "delete-form" = "/admin/bat/unit-bundles/manage/{bat_unit_bundle}/delete",
 *     "collection" = "/admin/bat/unit-bundles",
 *   },
 *   config_export = {
 *     "name",
 *     "type",
 *   }
 * )
 */
class UnitBundle extends ConfigEntityBundleBase implements UnitBundleInterface {

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
