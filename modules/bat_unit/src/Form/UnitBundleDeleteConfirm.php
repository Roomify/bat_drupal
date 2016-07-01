<?php

/**
 * @file
 * Contains \Drupal\bat_unit\Form\UnitBundleDeleteConfirm.
 */

namespace Drupal\bat_unit\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for unit bundle deletion.
 */
class UnitBundleDeleteConfirm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $entity = $this->getEntity();

    return $form;
  }

}
