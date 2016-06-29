<?php

namespace Drupal\bat_unit\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\bat_unit\UnitBundleInterface;

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
        'tags' => $this->entityManager()->getDefinition('bat_unit_bundle')->getListCacheTags(),
      ],
    ];

    $content = array();

    // Only use unit bundles the user has access to.
    foreach ($this->entityManager()->getStorage('bat_unit_bundle')->loadMultiple() as $type) {
      $access = $this->entityManager()->getAccessControlHandler('bat_unit')->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }
    }

    // Bypass the add listing if only one unit bundle is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('entity.bat_unit.add_form', array('unit_bundle' => $type->id()));
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
    $unit = $this->entityManager()->getStorage('bat_unit')->create(array(
      'type' => $unit_bundle->id(),
    ));

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
    return $this->t('Create @name', array('@name' => $unit_bundle->label()));
  }

}
