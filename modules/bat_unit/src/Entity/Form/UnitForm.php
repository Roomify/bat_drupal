<?php

/**
 * @file
 * Contains \Drupal\bat_unit\Entity\Form\UnitForm.
 */

namespace Drupal\bat_unit\Entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;
use Drupal\bat_unit\UnitInterface;

/**
 * Form controller for Unit edit forms.
 *
 * @ingroup bat
 */
class UnitForm extends ContentEntityForm {
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\bat\Entity\Unit */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    $form['changed'] = [
      '#type' => 'hidden',
      '#default_value' => $entity->getChangedTime(),
    ];

    $form['langcode'] = [
      '#title' => $this->t('Language'),
      '#type' => 'language_select',
      '#default_value' => $entity->getUntranslated()->language()->getId(),
      '#languages' => Language::STATE_ALL,
    ];

    $form['#theme'] = ['bat_entity_edit_form'];
    $form['#attached']['library'][] = 'bat/bat_ui';

    $form['advanced'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['entity-meta']],
      '#weight' => 99,
    ];

    $is_new = !$entity->isNew() ? format_date($entity->getChangedTime(), 'short') : t('Not saved yet');
    $form['meta'] = [
      '#attributes' => ['class' => ['entity-meta__header']],
      '#type' => 'container',
      '#group' => 'advanced',
      '#weight' => -100,
      'published' => [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $entity->getStatus() ? t('Published') : t('Not published'),
        '#access' => !$entity->isNew(),
        '#attributes' => [
          'class' => 'entity-meta__title',
        ],
      ],
      'changed' => [
        '#type' => 'item',
        '#wrapper_attributes' => ['class' => ['entity-meta__last-saved', 'container-inline']],
        '#markup' => '<h4 class="label inline">' . t('Last saved') . '</h4> ' . $is_new,
      ],
      'author' => [
        '#type' => 'item',
        '#wrapper_attributes' => ['class' => ['author', 'container-inline']],
        '#markup' => '<h4 class="label inline">' . t('Author') . '</h4> ' . $entity->getOwner()->getUsername(),
      ],
    ];

    $form['author'] = [
      '#type' => 'details',
      '#title' => t('Authoring information'),
      '#group' => 'advanced',
      '#attributes' => [
        'class' => ['type-form-author'],
      ],
      '#weight' => 90,
      '#optional' => TRUE,
      '#open' => TRUE,
    ];

    if (isset($form['uid'])) {
      $form['uid']['#group'] = 'author';
    }

    if (isset($form['created'])) {
      $form['created']['#group'] = 'author';
    }

    $form['#entity_builders']['update_status'] = [$this, 'updateStatus'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = $entity->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Unit.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Unit.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.bat_unit.edit_form', ['bat_unit' => $entity->id()]);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);
    $entity = $this->entity;

    // Add a "Publish" button.
    $element['publish'] = $element['submit'];
    // If the "Publish" button is clicked, we want to update the status to "published".
    $element['publish']['#published_status'] = TRUE;
    $element['publish']['#dropbutton'] = 'save';
    if ($entity->isNew()) {
      $element['publish']['#value'] = t('Save and publish');
    }
    else {
      $element['publish']['#value'] = $entity->getStatus() ? t('Save and keep published') : t('Save and publish');
    }
    $element['publish']['#weight'] = 0;

    // Add a "Unpublish" button.
    $element['unpublish'] = $element['submit'];
    // If the "Unpublish" button is clicked, we want to update the status to "unpublished".
    $element['unpublish']['#published_status'] = FALSE;
    $element['unpublish']['#dropbutton'] = 'save';
    if ($entity->isNew()) {
      $element['unpublish']['#value'] = t('Save as unpublished');
    }
    else {
      $element['unpublish']['#value'] = !$entity->getStatus() ? t('Save and keep unpublished') : t('Save and unpublish');
    }
    $element['unpublish']['#weight'] = 10;

    // If already published, the 'publish' button is primary.
    if ($entity->getStatus()) {
      unset($element['unpublish']['#button_type']);
    }
    // Otherwise, the 'unpublish' button is primary and should come first.
    else {
      unset($element['publish']['#button_type']);
      $element['unpublish']['#weight'] = -10;
    }

    // Remove the "Save" button.
    $element['submit']['#access'] = FALSE;

    return $element;
  }

  /**
   * Entity builder updating the unit status with the submitted value.
   *
   * @param string $entity_type_id
   *   The entity type identifier.
   * @param \Drupal\bat_unit\UnitInterface $unit
   *   The unit updated with the submitted values.
   * @param array $form
   *   The complete form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  function updateStatus($entity_type_id, UnitInterface $unit, array $form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();
    if (isset($element['#published_status'])) {
      $unit->setStatus($element['#published_status']);
    }
  }

}
