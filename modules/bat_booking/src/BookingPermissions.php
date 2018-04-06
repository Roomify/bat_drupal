<?php

/**
 * @file
 * Contains \Drupal\bat_booking\BookingPermissions.
 */

namespace Drupal\bat_booking;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class BookingPermissions implements ContainerInjectionInterface {

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

  public function permissions() {
    $permissions = [];

    $permissions += bat_entity_access_permissions('bat_booking');

    foreach (bat_booking_get_bundles() as $bundle_name => $bundle_info) {
      $permissions['view own booking entities of bundle ' . $bundle_name] = [
        'title' => t('View own %bundle @entity_bundle', ['@entity_bundle' => 'bookings', '%bundle' => $bundle_info->label()]),
      ];
      $permissions['view any booking entity of bundle ' . $bundle_name] = [
        'title' => t('View any %bundle @entity_bundle', ['@entity_bundle' => 'booking', '%bundle' => $bundle_info->label()]),
      ];
    }

    return $permissions;
  }

}
