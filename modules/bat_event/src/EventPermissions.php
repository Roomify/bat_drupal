<?php

/**
 * @file
 * Contains \Drupal\bat_event\EventPermissions.
 */

namespace Drupal\bat_event;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class EventPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new FilterPermissions instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * Returns an array of filter permissions.
   *
   * @return array
   */
  public function permissions() {
    $permissions = [];

    foreach (bat_event_get_types() as $bundle_name => $bundle_info) {
      $permissions['view calendar data for any ' . $bundle_name . ' event'] = [
        'title' => $this->t('View calendar data for any %bundle @entity_type', ['@entity_type' => 'events', '%bundle' => $bundle_info->label()]),
      ];
    }

    return $permissions + bat_entity_access_permissions('bat_event');
  }

}
