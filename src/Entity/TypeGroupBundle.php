<?php

/**
 * @file
 * Contains \Drupal\bat\Entity\TypeGroupBundle.
 */

namespace Drupal\bat\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\bat\TypeGroupBundleInterface;

/**
 * Defines the Type Group Bundle configuration entity.
 *
 * @ConfigEntityType(
 *   id = "bat_type_group_bundle",
 *   label = @Translation("Type Group Bundle"),
 *   handlers = {
 *     "access" = "Drupal\bat\TypeGroupBundleAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\bat\TypeGroupBundleForm",
 *       "edit" = "Drupal\bat\TypeGroupBundleForm",
 *       "delete" = "Drupal\bat\Form\TypeGroupBundleDeleteConfirm"
 *     },
 *     "list_builder" = "Drupal\bat\TypeGroupBundleListBuilder",
 *   },
 *   admin_permission = "administer type_group_bundle entities",
 *   config_prefix = "type_group_bundle",
 *   bundle_of = "bat_type_group",
 *   entity_keys = {
 *     "id" = "type",
 *     "label" = "name"
 *   },
 *   links = {
 *     "edit-form" = "/admin/bat/group-types/manage/{bat_type_group_bundle}",
 *     "delete-form" = "/admin/bat/group-types/manage/{bat_type_group_bundle}/delete",
 *     "collection" = "/admin/bat/group-types",
 *   },
 *   config_export = {
 *     "name",
 *     "type",
 *   }
 * )
 */
class TypeGroupBundle extends ConfigEntityBundleBase implements TypeGroupBundleInterface {

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
