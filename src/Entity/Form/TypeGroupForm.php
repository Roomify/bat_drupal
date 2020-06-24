<?php

/**
 * @file
 * Contains \Drupal\bat\Entity\Form\TypeGroupForm.
 */

namespace Drupal\bat\Entity\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;
use Drupal\bat\TypeGroupInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Type Group edit forms.
 *
 * @ingroup bat
 */
class TypeGroupForm extends ContentEntityForm {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a TypeGroupForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, DateFormatterInterface $date_formatter, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('date.formatter'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\bat\Entity\TypeGroup */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    $form['changed'] = [
      '#type' => 'hidden',
      '#default_value' => $entity->getChangedTime(),
    ];

    $form['#theme'] = ['bat_entity_edit_form'];
    $form['#attached']['library'][] = 'bat/bat_ui';

    $form['advanced'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['entity-meta']],
      '#weight' => 99,
    ];

    $is_new = !$entity->isNew() ? $this->dateFormatter->format($entity->getChangedTime(), 'short') : t('Not saved yet');
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
        '#markup' => '<h4 class="label inline">' . t('Author') . '</h4> ' . $entity->getOwner()->getDisplayName(),
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
        $this->messenger()->addMessage($this->t('Created the %label Property.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Property.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.bat_type_group.edit_form', ['bat_type_group' => $entity->id()]);

    return $status;
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
   * Entity builder updating the type group status with the submitted value.
   *
   * @param string $entity_type_id
   *   The entity type identifier.
   * @param \Drupal\bat\TypeGroupInterface $type_group
   *   The type group updated with the submitted values.
   * @param array $form
   *   The complete form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function updateStatus($entity_type_id, TypeGroupInterface $type_group, array $form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();
    if (isset($element['#published_status'])) {
      $type_group->setStatus($element['#published_status']);
    }
  }

}
