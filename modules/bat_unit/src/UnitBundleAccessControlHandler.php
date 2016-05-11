<?php

namespace Drupal\bat_unit;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the node type entity type.
 *
 * @see \Drupal\bat_unit\Entity\TypeBundle
 */
class UnitBundleAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'access content');
        break;

      case 'delete':
        return parent::checkAccess($entity, $operation, $account)->addCacheableDependency($entity);
        break;

      default:
        return parent::checkAccess($entity, $operation, $account);
        break;
    }
  }

}
