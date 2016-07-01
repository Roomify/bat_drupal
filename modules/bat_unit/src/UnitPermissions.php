<?php

/**
 * @file
 * Contains \Drupal\bat_unit\UnitPermissions.
 */

namespace Drupal\bat_unit;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UnitPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a new FilterPermissions instance.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.manager'));
  }

  /**
   * Returns an array of filter permissions.
   *
   * @return array
   */
  public function permissions() {
    $permissions = [];

    $permissions += bat_entity_access_permissions('bat_unit');
    $permissions += bat_entity_access_permissions('bat_unit_type');

    foreach (bat_unit_get_bundles() as $bundle_name => $bundle_info) {
      $permissions['view own unit entities of bundle ' . $bundle_name] = array(
        'title' => t('View own published %bundle @entity_bundle', array('@entity_bundle' => 'units', '%bundle' => $bundle_info->label())),
      );
      $permissions['view any unit entity of bundle ' . $bundle_name] = array(
        'title' => t('View any published %bundle @entity_bundle', array('@entity_bundle' => 'unit', '%bundle' => $bundle_info->label())),
      );
    }

    foreach (bat_unit_get_type_bundles() as $bundle_name => $bundle_info) {
      $permissions['view own unit_type entities of bundle ' . $bundle_name] = array(
        'title' => t('View own published %bundle @entity_bundle', array('@entity_bundle' => 'types', '%bundle' => $bundle_info->label())),
      );
      $permissions['view any unit_type entity of bundle ' . $bundle_name] = array(
        'title' => t('View any published %bundle @entity_bundle', array('@entity_bundle' => 'type', '%bundle' => $bundle_info->label())),
      );
    }

    return $permissions;
  }

}
