<?php

/**
 * @file
 * Contains \Drupal\bat\TypeGroupBundleAccessControlHandler.
 */

namespace Drupal\bat;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the type group bundle entity type.
 *
 * @see \Drupal\bat\Entity\TypeGroupBundle
 */
class TypeGroupBundleAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'access content');

      case 'delete':
        return parent::checkAccess($entity, $operation, $account)->addCacheableDependency($entity);

      default:
        return parent::checkAccess($entity, $operation, $account);
    }
  }

}
