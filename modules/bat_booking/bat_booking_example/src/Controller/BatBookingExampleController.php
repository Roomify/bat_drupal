<?php

/**
 * @file
 * Contains \Drupal\bat_booking_example\Controller\BatBookingExampleController.
 */

namespace Drupal\bat_booking_example\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 *
 */
class BatBookingExampleController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Constructs a BatBookingExampleController object.
   */
  public function __construct() {
  }

  public function batBookingConfirmationPage($start_date, $end_date, $type_id) {
    $header = $start_date->format('Y-m-d') . ' - ' . $end_date->format('Y-m-d');
    $form = $this->formBuilder()->getForm('Drupal\bat_booking_example\Form\BookingConfirmationForm', $start_date, $end_date, $type_id);

    return [
      '#theme' => 'booking_confirmation_page',
      '#header' => $header,
      '#form' => $form,
    ];
  }

}
