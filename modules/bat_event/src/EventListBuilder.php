<?php

/**
 * @file
 * Contains \Drupal\bat_event\EventListBuilder.
 */

namespace Drupal\bat_event;

use Drupal\Core\Config\ConfigFactoryInterface;
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
class EventListBuilder extends EntityListBuilder {

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new EventListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The entity query factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, QueryFactory $query_factory, ConfigFactoryInterface $config_factory) {
    parent::__construct($entity_type, $storage);
    $this->queryFactory = $query_factory;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('entity.query'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entity_query = $this->queryFactory->get('bat_event');
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
      'start_date' => [
        'data' => $this->t('Start Date'),
        'field' => 'event_dates',
        'specifier' => 'start',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'end_date' => [
        'data' => $this->t('End Date'),
        'field' => 'event_dates',
        'specifier' => 'end',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'type' => [
        'data' => $this->t('Type'),
        'field' => 'type',
        'specifier' => 'type',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $date_format = $this->configFactory->get('bat.settings')->get('bat_date_format') ?: 'Y-m-d H:i';

    $row['id'] = $entity->id();
    $row['start_date'] = $entity->getStartDate()->format($date_format);
    $row['end_date'] = $entity->getEndDate()->format($date_format);
    $row['type'] = bat_event_type_load($entity->bundle())->label();
    return $row + parent::buildRow($entity);
  }

}
