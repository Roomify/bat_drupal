<?php

/**
 * @file
 * Contains \Drupal\bat_event\StateAccessControlHandler.
 */

namespace Drupal\bat_event;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the state entity type.
 *
 * @see \Drupal\bat_event\Entity\State
 */
class StateAccessControlHandler extends EntityAccessControlHandler {

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
