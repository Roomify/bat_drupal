<?php

/**
 * @file
 * Contains \Drupal\bat_event\Entity\Form\StateForm.
 */

namespace Drupal\bat_event\Entity\Form;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Color;

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

    $form['machine_name'] = [
      '#type' => 'machine_name',
      '#default_value' => $state->getMachineName(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => FALSE,
      '#machine_name' => [
        'exists' => ['Drupal\bat_event\Entity\State', 'loadByMachineName'],
        'source' => ['name', 'widget', '0', 'value'],
      ],
      '#description' => t('A unique machine-readable name for this state. It must only contain lowercase letters, numbers, and underscores.'),
    ];

    $form['color'] = [
      '#type' => 'textfield',
      '#title' => t('Color'),
      '#size' => 12,
      '#maxlength' => 7,
      '#default_value' => $state->getColor(),
      '#dependency' => ['edit-row-options-colors-legend' => ['type']],
      '#prefix' => '<div class="bat-colorpicker-wrapper form-wrapper">',
      '#suffix' => '<div class="bat-colorpicker"></div></div>',
      '#attributes' => ['class' => ['bat-edit-colorpicker']],
      '#attached' => [
        'library' => [
          'bat_event/color',
        ],
      ],
      '#required' => TRUE,
    ];

    $form['calendar_label'] = [
      '#type' => 'textfield',
      '#title' => t('Calendar label'),
      '#size' => 10,
      '#maxlength' => 50,
      '#default_value' => $state->getCalendarLabel(),
      '#required' => TRUE,
    ];

    $form['blocking'] = [
      '#type' => 'checkbox',
      '#title' => t('Blocking'),
      '#default_value' => $state->getBlocking(),
    ];

    if (!$state->isNew() && $this->entity->getEventType()) {
      $form['event_type']['#access'] = FALSE;
    }

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if (!$form_state->isValueEmpty('color') && !Color::validateHex($form_state->getValue('color'))) {
      $form_state->setErrorByName('color', $this->t('Color must be a hexadecimal color value.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $state = $this->entity;
    $status = $state->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label State.', [
          '%label' => $state->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label State.', [
          '%label' => $state->label(),
        ]));
    }

    $form_state->setRedirect('entity.state.edit_form', ['state' => $state->id()]);
  }

}
