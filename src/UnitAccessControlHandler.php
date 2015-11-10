<?php

/**
 * @file
 * Contains \Drupal\bat\UnitAccessControlHandler.
 */

namespace Drupal\bat;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Unit entity.
 *
 * @see \Drupal\bat\Entity\Unit.
 */
class UnitAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view unit entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit unit entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete unit entities');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add unit entities');
  }

}
