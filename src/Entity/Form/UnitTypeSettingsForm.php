<?php

/**
 * @file
 * Contains \Drupal\bat\Entity\Form\UnitTypeSettingsForm.
 */

namespace Drupal\bat\Entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class UnitTypeSettingsForm.
 *
 * @package Drupal\bat\Form
 *
 * @ingroup bat
 */
class UnitTypeSettingsForm extends FormBase {
  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'UnitType_settings';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Empty implementation of the abstract submit class.
  }


  /**
   * Defines the settings form for Unit type entities.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['UnitType_settings']['#markup'] = 'Settings form for Unit type entities. Manage field settings here.';
    return $form;
  }

}
