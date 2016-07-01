<?php

/**
 * @file
 * Contains \Drupal\bat_unit\Plugin\Action\PublishUnit.
 */

namespace Drupal\bat_unit\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Publishes a unit.
 *
 * @Action(
 *   id = "unit_publish_action",
 *   label = @Translation("Publish selected unit"),
 *   type = "bat_unit"
 * )
 */
class PublishUnit extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $entity->status = 1;
    $entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $result = $object->access('update', $account, TRUE)
      ->andIf($object->status->access('edit', $account, TRUE));

    return $return_as_object ? $result : $result->isAllowed();
  }

}
