<?php

namespace Drupal\bat_event\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\bat_event\EventTypeInterface;

/**
 * Determines access to for node add pages.
 */
class EventAddAccessCheck implements AccessInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a EntityCreateAccessCheck object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * Checks access to the node add page for the node type.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\bat_event\EventTypeInterface $event_type
   *   (optional) The node type. If not specified, access is allowed if there
   *   exists at least one node type for which the user may create a node.
   *
   * @return string
   *   A \Drupal\Core\Access\AccessInterface constant value.
   */
  public function access(AccountInterface $account, EventTypeInterface $event_type = NULL) {
    $access_control_handler = $this->entityManager->getAccessControlHandler('bat_event');

    if ($account->hasPermission('administer bat_event_type entities')) {
      // There are no type bundles defined that the user has permission to
      // create, but the user does have the permission to administer the content
      // types, so grant them access to the page anyway.
      return AccessResult::allowed();
    }

    if ($event_type) {
      return $access_control_handler->createAccess($event_type->id(), $account, [], TRUE);
    }

    $bundles = bat_event_get_types();
    foreach ($bundles as $bundle) {
      if (bat_event_access(bat_event_create(array('type' => $bundle->id(), 'uid' => 0)), 'create', $account->getAccount())) {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }

}
