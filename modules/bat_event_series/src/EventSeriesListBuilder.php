<?php

/**
 * @file
 * Contains \Drupal\bat_event_series\EventSeriesListBuilder.
 */

namespace Drupal\bat_event_series;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of Event entities.
 *
 * @ingroup bat
 */
class EventSeriesListBuilder extends EntityListBuilder {

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * Constructs a new EventListBuilder object.
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
    $entity_query = $this->queryFactory->get('bat_event_series');
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
    $header = [
      'id' => [
        'data' => $this->t('Event ID'),
        'field' => 'id',
        'specifier' => 'id',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'label' => [
        'data' => $this->t('Label'),
        'field' => 'label',
        'specifier' => 'label',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'rrule' => [
        'data' => $this->t('RRule'),
        'field' => 'rrule',
        'specifier' => 'rrule',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = $entity->id();
    $row['label'] = $entity->label();
    $row['rrule'] = $entity->getRRule();
    return $row + parent::buildRow($entity);
  }

}
