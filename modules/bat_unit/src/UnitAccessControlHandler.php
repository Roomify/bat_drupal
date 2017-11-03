<?php

/**
 * @file
 * Contains \Drupal\bat_unit\UnitAccessControlHandler.
 */

namespace Drupal\bat_unit;

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
  protected function checkCreateAccess(AccountInterface $account, array $context, $unit_bundle = NULL) {
    return bat_unit_access(bat_unit_create(['type' => $unit_bundle]), 'create', $account);
  }

}
