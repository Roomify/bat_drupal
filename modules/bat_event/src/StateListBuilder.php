<?php

/**
 * @file
 * Contains \Drupal\bat_event\StateListBuilder.
 */

namespace Drupal\bat_event;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a listing of State entities.
 *
 * @ingroup bat
 */
class StateListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * Constructs a new StateListBuilder object.
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
    $entity_query = $this->queryFactory->get('state');
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
    $header['id'] = $this->t('State ID');
    $header['name'] = $this->t('Name');
    $header['color'] = $this->t('Color');
    $header['calendar_label'] = $this->t('Calendar label');
    $header['blocking'] = $this->t('Blocking');
    $header['event_type'] = $this->t('Event type');

    $header = [
      'id' => [
        'data' => $this->t('State ID'),
        'field' => 'id',
        'specifier' => 'id',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'name' => [
        'data' => $this->t('Name'),
        'field' => 'name',
        'specifier' => 'name',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'color' => [
        'data' => $this->t('Color'),
        'field' => 'color',
        'specifier' => 'color',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'calendar_label' => [
        'data' => $this->t('Calendar label'),
        'field' => 'calendar_label',
        'specifier' => 'calendar_label',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'blocking' => [
        'data' => $this->t('Blocking'),
        'field' => 'blocking',
        'specifier' => 'blocking',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'event_type' => [
        'data' => $this->t('Event type'),
        'field' => 'event_type',
        'specifier' => 'event_type',
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
    $row['name'] = $this->l(
      $this->getLabel($entity),
      new Url(
        'entity.state.edit_form', [
          'state' => $entity->id(),
        ]
      )
    );
    $row['color'] = $entity->getColor();
    $row['calendar_label'] = $entity->getCalendarLabel();
    $row['blocking'] = ($entity->getBlocking()) ? t('Blocking') : t('Not blocking');
    $row['event_type'] = $entity->getEventType()->label();
    return $row + parent::buildRow($entity);
  }

}
