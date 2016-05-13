<?php

namespace Drupal\bat\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\bat\PropertyTypeInterface;

/**
 * Defines the Unit Bundle configuration entity.
 *
 * @ConfigEntityType(
 *   id = "property_type",
 *   label = @Translation("Property Type"),
 *   handlers = {
 *     "access" = "Drupal\bat\PropertyTypeAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\bat\PropertyTypeForm",
 *       "edit" = "Drupal\bat\PropertyTypeForm",
 *       "delete" = "Drupal\bat\Form\PropertyTypeDeleteConfirm"
 *     },
 *     "list_builder" = "Drupal\bat\PropertyTypeListBuilder",
 *   },
 *   admin_permission = "administer content types",
 *   config_prefix = "property_type",
 *   bundle_of = "property",
 *   entity_keys = {
 *     "id" = "type",
 *     "label" = "name"
 *   },
 *   links = {
 *     "edit-form" = "/admin/bat/property-types/manage/{unit_bundle}",
 *     "delete-form" = "/admin/bat/property-types/manage/{unit_bundle}/delete",
 *     "collection" = "/admin/bat/property-types",
 *   },
 *   config_export = {
 *     "name",
 *     "type",
 *   }
 * )
 */
class PropertyType extends ConfigEntityBundleBase implements PropertyTypeInterface {

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
