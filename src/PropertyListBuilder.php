<?php

/**
 * @file
 * Contains \Drupal\bat\PropertyListBuilder.
 */

namespace Drupal\bat;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Property entities.
 *
 * @ingroup bat
 */
class PropertyListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Property ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\bat\Entity\Property */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $this->getLabel($entity),
      new Url(
        'entity.property.edit_form', array(
          'property' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
