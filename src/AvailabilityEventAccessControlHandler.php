<?php

/**
 * @file
 * Contains \Drupal\bat\AvailabilityEventAccessControlHandler.
 */

namespace Drupal\bat;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Availability Event entity.
 *
 * @see \Drupal\bat\Entity\AvailabilityEvent.
 */
class AvailabilityEventAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view availability event entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit availability event entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete availability event entities');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add availability event entities');
  }

}
