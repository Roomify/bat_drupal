<?php

/**
 * @file
 * Contains \Drupal\bat\Access\TypeGroupAddAccessCheck.
 */

namespace Drupal\bat\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\bat\TypeGroupBundleInterface;

/**
 * Determines access to for type group add pages.
 */
class TypeGroupAddAccessCheck implements AccessInterface {

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
   * Checks access to the type group add page for the type group bundle.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\bat\TypeGroupBundleInterface $type_group_bundle
   *   (optional) The type group bundle. If not specified, access is allowed if there
   *   exists at least one type group bundle for which the user may create a type group.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, TypeGroupBundleInterface $type_group_bundle = NULL) {
    $access_control_handler = $this->entityTypeManager->getAccessControlHandler('bat_type_group');

    if ($account->hasPermission('administer bat_type_group_bundle entities')) {
      // There are no type bundles defined that the user has permission to
      // create, but the user does have the permission to administer the content
      // types, so grant them access to the page anyway.
      return AccessResult::allowed();
    }

    if ($type_group_bundle) {
      return $access_control_handler->createAccess($type_group_bundle->id(), $account, [], TRUE);
    }

    $bundles = bat_type_group_get_bundles();
    foreach ($bundles as $bundle) {
      if (bat_entity_access(bat_type_group_create(['type' => $bundle->id(), 'uid' => 0]), 'create', $account->getAccount())->isAllowed()) {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }

}
