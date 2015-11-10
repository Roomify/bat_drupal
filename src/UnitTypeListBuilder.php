<?php

/**
 * @file
 * Contains \Drupal\bat\UnitTypeListBuilder.
 */

namespace Drupal\bat;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Unit type entities.
 *
 * @ingroup bat
 */
class UnitTypeListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Unit type ID');
    $header['name'] = $this->t('Name');
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
        'entity.unit_type.edit_form', array(
          'unit_type' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
