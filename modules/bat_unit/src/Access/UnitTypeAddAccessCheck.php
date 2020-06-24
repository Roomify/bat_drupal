<?php

/**
 * @file
 * Contains \Drupal\bat_unit\Access\UnitTypeAddAccessCheck.
 */

namespace Drupal\bat_unit\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\bat_unit\TypeBundleInterface;

/**
 * Determines access to for unit type add pages.
 */
class UnitTypeAddAccessCheck implements AccessInterface {

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
   * Checks access to the unit type add page for the type bundle.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\bat_unit\TypeBundleInterface $type_bundle
   *   (optional) The type bundle. If not specified, access is allowed if there
   *   exists at least one type bundle for which the user may create a unit type.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, TypeBundleInterface $type_bundle = NULL) {
    $access_control_handler = $this->entityTypeManager->getAccessControlHandler('bat_unit_type');

    if ($account->hasPermission('administer bat_type_bundle entities')) {
      // There are no type bundles defined that the user has permission to
      // create, but the user does have the permission to administer the content
      // types, so grant them access to the page anyway.
      return AccessResult::allowed();
    }

    if ($type_bundle) {
      return $access_control_handler->createAccess($type_bundle->id(), $account, [], TRUE);
    }

    $bundles = bat_unit_get_type_bundles();
    foreach ($bundles as $bundle) {
      if (bat_type_access(bat_type_create(['type' => $bundle->id(), 'uid' => 0]), 'create', $account->getAccount())) {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }

}
