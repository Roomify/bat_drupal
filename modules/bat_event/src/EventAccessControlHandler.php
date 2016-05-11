<?php

/**
 * @file
 * Contains \Drupal\bat_event\EventAccessControlHandler.
 */

namespace Drupal\bat_event;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Event entity.
 *
 * @see \Drupal\bat_event\Entity\Event.
 */
class EventAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view event entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit event entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete event entities');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add event entities');
  }

}
