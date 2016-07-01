<?php

/**
 * @file
 * Contains \Drupal\bat_unit\Plugin\Action\UnpublishUnit.
 */

namespace Drupal\bat_unit\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Unpublishes a unit.
 *
 * @Action(
 *   id = "unit_unpublish_action",
 *   label = @Translation("Unpublish selected unit"),
 *   type = "bat_unit"
 * )
 */
class UnpublishUnit extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $entity->status = 0;
    $entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $access = $object->access('update', $account, TRUE)
      ->andIf($object->status->access('edit', $account, TRUE));

    return $return_as_object ? $access : $access->isAllowed();
  }

}
