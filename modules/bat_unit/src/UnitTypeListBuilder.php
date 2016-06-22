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
    $header['type'] = $this->t('Type');
    $header['status'] = $this->t('Status');
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
