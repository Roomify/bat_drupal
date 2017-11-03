<?php

/**
 * @file
 * Contains \Drupal\bat_unit\Controller\TypeController.
 */

namespace Drupal\bat_unit\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\bat_unit\TypeBundleInterface;

/**
 * Returns responses for Type routes.
 */
class TypeController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Constructs a TypeController object.
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
      '#theme' => 'bat_type_add_list',
      '#cache' => [
        'tags' => $this->entityTypeManager()->getDefinition('bat_type_bundle')->getListCacheTags(),
      ],
    ];

    $content = [];

    // Only use type bundles the user has access to.
    foreach ($this->entityTypeManager()->getStorage('bat_type_bundle')->loadMultiple() as $type) {
      $access = $this->entityTypeManager()->getAccessControlHandler('bat_unit_type')->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }
    }

    // Bypass the add listing if only one unit type bundle is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('entity.bat_unit_type.add', ['type_bundle' => $type->id()]);
    }

    $build['#content'] = $content;

    return $build;
  }

  /**
   * Provides the unit type submission form.
   *
   * @param \Drupal\bat_unit\TypeBundleInterface $type_bundle
   *   The type bundle entity for the unit type.
   *
   * @return array
   *   A unit type submission form.
   */
  public function add(TypeBundleInterface $type_bundle) {
    $type = $this->entityTypeManager()->getStorage('bat_unit_type')->create([
      'type' => $type_bundle->id(),
    ]);

    $form = $this->entityFormBuilder()->getForm($type);

    return $form;
  }

  /**
   * The _title_callback for the type.add route.
   *
   * @param \Drupal\bat_unit\TypeBundleInterface $type_bundle
   *   The current type bundle.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(TypeBundleInterface $type_bundle) {
    return $this->t('Create @name', ['@name' => $type_bundle->label()]);
  }

}
