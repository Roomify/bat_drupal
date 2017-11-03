<?php

/**
 * @file
 * Contains \Drupal\bat_unit\Controller\UnitController.
 */

namespace Drupal\bat_unit\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\bat_unit\UnitBundleInterface;
use Drupal\bat_unit\UnitTypeInterface;

/**
 * Returns responses for Type routes.
 */
class UnitController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Constructs a UnitController object.
   */
  public function __construct() {
  }

  /**
   * Displays add content links for available unit bundles.
   *
   * Redirects to admin/bat/config/unit/add/[type] if only one unit bundle is available.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A render array for a list of the unit bundles that can be added; however,
   *   if there is only one unit bundle defined for the site, the function
   *   will return a RedirectResponse to the unit add page for that one unit bundle.
   */
  public function addPage() {
    $build = [
      '#theme' => 'bat_unit_add_list',
      '#cache' => [
        'tags' => $this->entityTypeManager()->getDefinition('bat_unit_bundle')->getListCacheTags(),
      ],
    ];

    $content = [];

    // Only use unit bundles the user has access to.
    foreach ($this->entityTypeManager()->getStorage('bat_unit_bundle')->loadMultiple() as $type) {
      $access = $this->entityTypeManager()->getAccessControlHandler('bat_unit')->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }
    }

    // Bypass the add listing if only one unit bundle is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('entity.bat_unit.add_form', ['unit_bundle' => $type->id()]);
    }

    $build['#content'] = $content;

    return $build;
  }

  /**
   * Provides the unit submission form.
   *
   * @param \Drupal\bat_unit\UnitBundleInterface $unit_bundle
   *   The unit bundle entity for the unit.
   *
   * @return array
   *   A unit submission form.
   */
  public function add(UnitBundleInterface $unit_bundle) {
    $unit = $this->entityTypeManager()->getStorage('bat_unit')->create([
      'type' => $unit_bundle->id(),
    ]);

    $form = $this->entityFormBuilder()->getForm($unit);

    return $form;
  }

  /**
   * The _title_callback for the unit.add route.
   *
   * @param \Drupal\bat_unit\UnitBundleInterface $unit_bundle
   *   The current unit bundle.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(UnitBundleInterface $unit_bundle) {
    return $this->t('Create @name', ['@name' => $unit_bundle->label()]);
  }

  /**
   *
   */
  public function listUnits(UnitTypeInterface $unit_type) {

  }

  /**
   *
   */
  public function addUnits(UnitTypeInterface $unit_type) {

  }

}
