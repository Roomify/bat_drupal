<?php

/**
 * @file
 * Contains \Drupal\bat_event\Entity\Form\StateForm.
 */

namespace Drupal\bat_event\Entity\Form;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;

/**
 * Class StateForm.
 *
 * @ingroup bat
 */
class StateForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $state = $this->entity;

    $form['machine_name'] = array(
      '#type' => 'machine_name',
      '#default_value' => $state->getMachineName(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => FALSE,
      '#machine_name' => array(
        'exists' => ['Drupal\bat_event\Entity\State', 'load'],
        'source' => array('name'),
      ),
      '#description' => t('A unique machine-readable name for this state. It must only contain lowercase letters, numbers, and underscores.'),
    );

    $form['color'] = array(
      '#type' => 'textfield',
      '#title' => t('Color'),
      '#size' => 12,
      '#maxlength' => 7,
      '#default_value' => $state->getColor(),
      '#element_validate' => array('bat_event_validate_hex_color'),
      '#dependency' => array('edit-row-options-colors-legend' => array('type')),
      '#prefix' => '<div class="bat-colorpicker-wrapper form-wrapper">',
      '#suffix' => '<div class="bat-colorpicker"></div></div>',
      '#attributes' => array('class' => array('bat-edit-colorpicker')),
      '#attached' => array(
        'library' => array(
          'bat_event/color',
        ),
      ),
      '#required' => TRUE,
    );

    $form['calendar_label'] = array(
      '#type' => 'textfield',
      '#title' => t('Calendar label'),
      '#size' => 10,
      '#maxlength' => 50,
      '#default_value' => $state->getCalendarLabel(),
      '#required' => TRUE,
    );

    $form['blocking'] = array(
      '#type' => 'checkbox',
      '#title' => t('Blocking'),
      '#default_value' => $state->getBlocking(),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, FormStateInterface $form_state) {
    // Build the entity object from the submitted values.
    $entity = parent::submit($form, $form_state);

    return $entity;
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

    $form_state->setRedirect('entity.state.edit_form', ['state' => $state->id()]);
  }

}
