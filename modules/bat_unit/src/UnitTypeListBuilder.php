<?php

/**
 * @file
 * Contains \Drupal\bat_unit\UnitTypeListBuilder.
 */

namespace Drupal\bat_unit;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of Unit type entities.
 *
 * @ingroup bat
 */
class UnitTypeListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * Constructs a new UnitTypeListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The entity query factory.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, QueryFactory $query_factory) {
    parent::__construct($entity_type, $storage);
    $this->queryFactory = $query_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entity_query = $this->queryFactory->get('bat_unit_type');
    $entity_query->pager(50);

    $header = $this->buildHeader();
    $entity_query->tableSort($header);

    $eventids = $entity_query->execute();

    return $this->storage->loadMultiple($eventids);
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = array(
      'id' => array(
        'data' => $this->t('Unit type ID'),
        'field' => 'id',
        'specifier' => 'id',
        'class' => array(RESPONSIVE_PRIORITY_LOW),
      ),
      'name' => array(
        'data' => $this->t('Name'),
        'field' => 'name',
        'specifier' => 'name',
        'class' => array(RESPONSIVE_PRIORITY_LOW),
      ),
      'type' => array(
        'data' => $this->t('Type'),
        'field' => 'type',
        'specifier' => 'type',
        'class' => array(RESPONSIVE_PRIORITY_LOW),
      ),
      'status' => array(
        'data' => $this->t('Status'),
        'field' => 'status',
        'specifier' => 'status',
        'class' => array(RESPONSIVE_PRIORITY_LOW),
      ),
    );
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\bat\Entity\UnitType */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $this->getLabel($entity),
      new Url(
        'entity.bat_unit_type.edit_form', array(
          'bat_unit_type' => $entity->id(),
        )
      )
    );
    $row['bundle'] = bat_type_bundle_load($entity->bundle())->label();
    $row['status'] = ($entity->getStatus()) ? t('Published') : t('Unpublished');
    return $row + parent::buildRow($entity);
  }

}
