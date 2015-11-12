<?php

/**
 * @file
 * Contains \Drupal\bat\Form\AvailabilityStateForm.
 */

namespace Drupal\bat\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AvailabilityStateForm.
 *
 * @package Drupal\bat\Form
 */
class AvailabilityStateForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $availability_state = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $availability_state->label(),
      '#description' => $this->t("Label for the Availability State."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $availability_state->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\bat\Entity\AvailabilityState::load',
      ),
      '#disabled' => !$availability_state->isNew(),
    );

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $availability_state = $this->entity;
    $status = $availability_state->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Availability State.', [
          '%label' => $availability_state->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Availability State.', [
          '%label' => $availability_state->label(),
        ]));
    }
    $form_state->setRedirectUrl($availability_state->urlInfo('collection'));
  }

}
