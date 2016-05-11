<?php

/**
 * @file
 * Contains \Drupal\bat_event\Form\StateForm.
 */

namespace Drupal\bat_event\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class StateForm.
 *
 * @package Drupal\bat\Form
 */
class StateForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $state = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $state->label(),
      '#description' => $this->t("Label for the State."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $state->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\bat\Entity\State::load',
      ),
      '#disabled' => !$state->isNew(),
    );

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $state = $this->entity;
    $status = $state->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label State.', [
          '%label' => $state->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label State.', [
          '%label' => $state->label(),
        ]));
    }
    $form_state->setRedirectUrl($state->urlInfo('collection'));
  }

}
