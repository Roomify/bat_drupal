<?php

/**
 * @file
 * Contains \Drupal\bat_unit\UnitTypeAccessControlHandler.
 */

namespace Drupal\bat_unit;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Unit type entity.
 *
 * @see \Drupal\bat_unit\Entity\UnitType.
 */
class UnitTypeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view unit type entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit unit type entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete unit type entities');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $type_bundle = NULL) {
    return bat_type_access(bat_type_create(['type' => $type_bundle]), 'create', $account);
  }

}
