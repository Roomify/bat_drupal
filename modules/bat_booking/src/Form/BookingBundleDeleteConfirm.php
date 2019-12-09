<?php

/**
 * @file
 * Contains \Drupal\bat_booking\Form\BookingBundleDeleteConfirm.
 */

namespace Drupal\bat_booking\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for unit type bundle deletion.
 */
class BookingBundleDeleteConfirm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $entity = $this->getEntity();

    // Check if types of a type bundle exist before allowing deletion.
    $type_bundle = $entity->id();

    if (count(bat_booking_load_multiple([], ['type' => $type_bundle]))) {
      // This type bundle has associated types, don't allow deletion.
      $this->messenger()->addError(t('This Bat Booking bundle has associated Types. Please delete all Types before attempting to delete this Bat Booking bundle.'));

      return [];
    }

    return $form;
  }

}
