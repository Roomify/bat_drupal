<?php

/**
 * @file
 * Contains \Drupal\bat_booking\Controller\BookingController.
 */

namespace Drupal\bat_booking\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\bat_booking\BookingBundleInterface;

/**
 * Returns responses for Type routes.
 */
class BookingController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Constructs a BookingController object.
   */
  public function __construct() {
  }

  /**
   * Displays add content links for available unit type bundles.
   *
   * Redirects to admin/bat/config/unit_type/add/[type] if only one unit type bundle is available.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A render array for a list of the type bundles that can be added; however,
   *   if there is only one type bundle defined for the site, the function
   *   will return a RedirectResponse to the type add page for that one type bundle.
   */
  public function addPage() {
    $build = [
      '#theme' => 'bat_booking_add_list',
      '#cache' => [
        'tags' => $this->entityTypeManager()->getDefinition('bat_booking_bundle')->getListCacheTags(),
      ],
    ];

    $content = [];

    // Only use type bundles the user has access to.
    foreach ($this->entityTypeManager()->getStorage('bat_booking_bundle')->loadMultiple() as $type) {
      $content[$type->id()] = $type;
    }

    // Bypass the add listing if only one booking bundle is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('entity.bat_booking.add', ['booking_bundle' => $type->id()]);
    }

    $build['#content'] = $content;

    return $build;
  }

  /**
   * Provides the unit type submission form.
   *
   * @param \Drupal\bat_booking\BookingBundleInterface $booking_bundle
   *   The type bundle entity for the unit type.
   *
   * @return array
   *   A unit type submission form.
   */
  public function add(BookingBundleInterface $booking_bundle) {
    $type = $this->entityTypeManager()->getStorage('bat_booking')->create([
      'type' => $booking_bundle->id(),
    ]);

    $form = $this->entityFormBuilder()->getForm($type);

    return $form;
  }

  /**
   * The _title_callback for the type.add route.
   *
   * @param \Drupal\bat_booking\BookingBundleInterface $booking_bundle
   *   The current booking bundle.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(BookingBundleInterface $booking_bundle) {
    return $this->t('Create @name', ['@name' => $booking_bundle->label()]);
  }

}
