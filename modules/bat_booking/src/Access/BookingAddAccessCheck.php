<?php

/**
 * @file
 * Contains \Drupal\bat_booking\Access\BookingAddAccessCheck.
 */

namespace Drupal\bat_booking\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\bat_booking\BookingBundleInterface;

/**
 * Determines access to for unit add pages.
 */
class BookingAddAccessCheck implements AccessInterface {

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
   * Checks access to the unit add page for the unit type.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\bat_booking\BookingBundleInterface $booking_bundle
   *   (optional) The unit bundle. If not specified, access is allowed if there
   *   exists at least one unit bundle for which the user may create a unit.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, BookingBundleInterface $booking_bundle = NULL) {
    $access_control_handler = $this->entityTypeManager->getAccessControlHandler('bat_booking');

    if ($account->hasPermission('administer bat_booking_bundle entities')) {
      // There are no type bundles defined that the user has permission to
      // create, but the user does have the permission to administer the content
      // types, so grant them access to the page anyway.
      return AccessResult::allowed();
    }

    if ($booking_bundle) {
      return $access_control_handler->createAccess($booking_bundle->id(), $account, [], TRUE);
    }

    $bundles = bat_booking_get_bundles();
    foreach ($bundles as $bundle) {
      if (bat_booking_access(bat_booking_create(['type' => $bundle->id(), 'uid' => 0]), 'create', $account->getAccount())->isAllowed()) {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }

}
