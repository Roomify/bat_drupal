<?php

/**
 * @file
 * Contains \Drupal\bat_unit\UnitListBuilder.
 */

namespace Drupal\bat_unit;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Unit entities.
 *
 * @ingroup bat
 */
class UnitListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Unit ID');
    $header['name'] = $this->t('Name');
    $header['type'] = $this->t('Type');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\bat\Entity\Unit */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $this->getLabel($entity),
      new Url(
        'entity.bat_unit.edit_form', array(
          'bat_unit' => $entity->id(),
        )
      )
    );
    $row['bundle'] = bat_unit_bundle_load($entity->bundle())->label();
    return $row + parent::buildRow($entity);
  }

}
