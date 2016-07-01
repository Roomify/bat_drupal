<?php

/**
 * @file
 * Contains \Drupal\bat_unit\Form\TypeBundleDeleteConfirm.
 */

namespace Drupal\bat_unit\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for unit type bundle deletion.
 */
class TypeBundleDeleteConfirm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $entity = $this->getEntity();

    // Check if types of a type bundle exist before allowing deletion.
    $type_bundle = $entity->id();

    if (count(bat_type_load_multiple(array(), array('type' => $type_bundle)))) {
      // This type bundle has associated types, don't allow deletion.
      drupal_set_message(t('This Bat Type bundle has associated Types. Please delete all Types before attempting to delete this Bat Type bundle.'), 'error');

      return array();
    }

    return $form;
  }

}
