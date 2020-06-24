<?php

/**
 * @file
 * Contains \Drupal\bat_event_series\Entity\Form\EventSeriesUpdateConfirmForm.
 */

namespace Drupal\bat_event_series\Entity\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class EventSeriesUpdateConfirmForm extends ConfirmFormBase {

  /**
   * @var \Drupal\bat_event_series\Entity\EventSeries
   */
  protected $bat_event_series;

  /**
   * The tempstore factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Constructs a DeleteMultiple form object.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory) {
    $this->tempStoreFactory = $temp_store_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_series_update_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return 'Update all upcoming events in this series?';
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.bat_event_series.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Update');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $bat_event_series = NULL) {
    $this->bat_event_series = $this->tempStoreFactory->get('event_series_update_confirm')->get($this->currentUser()->id());

    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('confirm')) {
      $this->bat_event_series->save();

      $this->messenger()->addMessage($this->t('Saved the %label Event series.', [
        '%label' => $this->bat_event_series->label(),
      ]));

      $form_state->setRedirect('entity.bat_event_series.edit_form', ['bat_event_series' => $this->bat_event_series->id()]);
    }
  }

}
