<?php

/**
 * @file
 * Contains \Drupal\bat\TypeGroupAccessControlHandler.
 */

namespace Drupal\bat;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Property entity.
 *
 * @see \Drupal\bat\Entity\TypeGroup.
 */
class TypeGroupAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view property entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit property entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete property entities');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $type_group_bundle = NULL) {
    return bat_entity_access(bat_type_group_create(['type' => $type_group_bundle]), 'create', $account);
  }

}
