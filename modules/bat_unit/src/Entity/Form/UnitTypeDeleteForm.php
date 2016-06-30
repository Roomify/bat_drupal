<?php

/**
 * @file
 * Contains \Drupal\bat_unit\Entity\Form\UnitTypeDeleteForm.
 */

namespace Drupal\bat_unit\Entity\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting Unit type entities.
 *
 * @ingroup bat
 */
class UnitTypeDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete entity %name?', array('%name' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.bat_unit_type.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();

    drupal_set_message($this->t('The Type @label has been deleted.', array('@label' => $this->entity->label())));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
