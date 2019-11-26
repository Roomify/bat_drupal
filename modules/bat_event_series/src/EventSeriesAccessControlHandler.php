<?php

/**
 * @file
 * Contains \Drupal\bat_event_series\EventSeriesAccessControlHandler.
 */

namespace Drupal\bat_event_series;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Event Series entity.
 *
 * @see \Drupal\bat_event_series\Entity\EventSeries.
 */
class EventSeriesAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view event_series entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit event_series entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete event_series entities');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $event_series_type = NULL) {
    return bat_event_series_access(bat_event_series_create(['type' => $event_series_type]), 'create', $account);
  }

}
