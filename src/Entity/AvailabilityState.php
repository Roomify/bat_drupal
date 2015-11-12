<?php

/**
 * @file
 * Contains \Drupal\bat\Entity\AvailabilityState.
 */

namespace Drupal\bat\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\bat\AvailabilityStateInterface;

/**
 * Defines the Availability State entity.
 *
 * @ConfigEntityType(
 *   id = "availability_state",
 *   label = @Translation("Availability State"),
 *   handlers = {
 *     "list_builder" = "Drupal\bat\AvailabilityStateListBuilder",
 *     "form" = {
 *       "add" = "Drupal\bat\Form\AvailabilityStateForm",
 *       "edit" = "Drupal\bat\Form\AvailabilityStateForm",
 *       "delete" = "Drupal\bat\Form\AvailabilityStateDeleteForm"
 *     }
 *   },
 *   config_prefix = "availability_state",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/bat/availability_state/{availability_state}",
 *     "edit-form" = "/admin/bat/availability_state/{availability_state}/edit",
 *     "delete-form" = "/admin/bat/availability_state/{availability_state}/delete",
 *     "collection" = "/admin/bat/visibility_group"
 *   }
 * )
 */
class AvailabilityState extends ConfigEntityBase implements AvailabilityStateInterface {
  /**
   * The Availability State ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Availability State label.
   *
   * @var string
   */
  protected $label;

}
