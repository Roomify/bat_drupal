<?php

/**
 * @file
 * Contains \Drupal\bat_booking\BookingAccessControlHandler.
 */

namespace Drupal\bat_booking;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Unit type entity.
 *
 * @see \Drupal\bat_booking\Entity\Booking.
 */
class BookingAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view booking entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit booking entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete booking entities');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $type_bundle = NULL) {
    return bat_booking_access(bat_booking_create(['type' => $type_bundle]), 'create', $account);
  }

}
