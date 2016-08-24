<?php

/**
 * @file
 * Contains \Drupal\bat_booking\Entity\BookingBundle.
 */

namespace Drupal\bat_booking\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\bat_booking\BookingBundleInterface;

/**
 * Defines the Booking Bundle configuration entity.
 *
 * @ConfigEntityType(
 *   id = "bat_booking_bundle",
 *   label = @Translation("Booking bundle"),
 *   handlers = {
 *     "access" = "Drupal\bat_booking\BookingBundleAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\bat_booking\BookingBundleForm",
 *       "edit" = "Drupal\bat_booking\BookingBundleForm",
 *       "delete" = "Drupal\bat_booking\Form\BookingBundleDeleteConfirm"
 *     },
 *     "list_builder" = "Drupal\bat_booking\BookingBundleListBuilder",
 *   },
 *   admin_permission = "administer booking_bundle entities",
 *   config_prefix = "booking_bundle",
 *   bundle_of = "bat_booking",
 *   entity_keys = {
 *     "id" = "type",
 *     "label" = "name"
 *   },
 *   links = {
 *     "edit-form" = "/admin/bat/booking-bundles/manage/{bat_booking_bundle}",
 *     "delete-form" = "/admin/bat/booking-bundles/manage/{bat_booking_bundle}/delete",
 *     "collection" = "/admin/bat/booking-bundles",
 *   },
 *   config_export = {
 *     "name",
 *     "type",
 *   }
 * )
 */
class BookingBundle extends ConfigEntityBundleBase implements BookingBundleInterface {

  /**
   * The machine name of this event type.
   *
   * @var string
   *
   * @todo Rename to $id.
   */
  protected $type;

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
