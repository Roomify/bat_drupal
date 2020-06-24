<?php

/**
 * @file
 * Contains \Drupal\bat_fullcalendar\Access\EventManagementAccessCheck.
 */

namespace Drupal\bat_fullcalendar\Access;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Determines access to for event add pages.
 */
class EventManagementAccessCheck implements AccessInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a EntityCreateAccessCheck object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * Checks access to the event add page for the event type.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, $entity_id, $event_type, $event_id, $start_date, $end_date) {
    if ($event_id == 0) {
      return bat_event_access(bat_event_create(['type' => $event_type]), 'create', $account);
    }
    else {
      $event = bat_event_load($event_id);
      return bat_event_access($event, 'update', $account);
    }
  }

}
