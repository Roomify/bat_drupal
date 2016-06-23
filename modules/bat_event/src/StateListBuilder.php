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

/**
 * Provides a listing of State entities.
 *
 * @ingroup bat
 */
class StateListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('State ID');
    $header['name'] = $this->t('Name');
    $header['color'] = $this->t('Color');
    $header['calendar_label'] = $this->t('Calendar label');
    $header['blocking'] = $this->t('Blocking');
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
        'entity.state.edit_form', array(
          'state' => $entity->id(),
        )
      )
    );
    $row['color'] = $entity->getColor();
    $row['calendar_label'] = $entity->getCalendarLabel();
    $row['blocking'] = ($entity->getBlocking()) ? t('Blocking') : t('Not blocking');
    return $row + parent::buildRow($entity);
  }

}
