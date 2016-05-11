<?php

/**
 * @file
 * Contains \Drupal\bat_event\Entity\State.
 */

namespace Drupal\bat_event\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\bat_event\StateInterface;

/**
 * Defines the State entity.
 *
 * @ConfigEntityType(
 *   id = "state",
 *   label = @Translation("State"),
 *   handlers = {
 *     "list_builder" = "Drupal\bat_event\StateListBuilder",
 *     "form" = {
 *       "add" = "Drupal\bat_event\Form\StateForm",
 *       "edit" = "Drupal\bat_event\Form\StateForm",
 *       "delete" = "Drupal\bat_event\Form\StateDeleteForm"
 *     }
 *   },
 *   config_prefix = "state",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/bat/state/{state}",
 *     "edit-form" = "/admin/bat/state/{state}/edit",
 *     "delete-form" = "/admin/bat/state/{state}/delete",
 *     "collection" = "/admin/bat/visibility_group"
 *   }
 * )
 */
class State extends ConfigEntityBase implements StateInterface {
  /**
   * The State ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The State label.
   *
   * @var string
   */
  protected $label;

}
