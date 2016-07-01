<?php

namespace Drupal\bat\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\bat\TypeGroupBundleInterface;

/**
 * Returns responses for Type Group routes.
 */
class TypeGroupController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Constructs a TypeGroupController object.
   */
  public function __construct() {
  }

  /**
   * Displays add content links for available type group bundles.
   *
   * Redirects to admin/bat/config/type-group/add/[type] if only one type group bundle is available.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A render array for a list of the type group bundles that can be added; however,
   *   if there is only one type group bundle defined for the site, the function
   *   will return a RedirectResponse to the type group add page for that one
   *   type group bundle.
   */
  public function addPage() {
    $build = [
      '#theme' => 'bat_type_group_add_list',
      '#cache' => [
        'tags' => $this->entityManager()->getDefinition('bat_type_group_bundle')->getListCacheTags(),
      ],
    ];

    $content = array();

    // Only use type group bundles the user has access to.
    foreach ($this->entityManager()->getStorage('bat_type_group_bundle')->loadMultiple() as $type) {
      $access = $this->entityManager()->getAccessControlHandler('bat_type_group')->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }
    }

    // Bypass the add listing if only one type group bundle is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('entity.bat_type_group.add_form', array('type_group_bundle' => $type->id()));
    }

    $build['#content'] = $content;

    return $build;
  }

  /**
   * Provides the type group submission form.
   *
   * @param \Drupal\bat\TypeGroupBundleInterface $type_group_bundle
   *   The type group bundle entity for the type group.
   *
   * @return array
   *   A type group submission form.
   */
  public function add(TypeGroupBundleInterface $type_group_bundle) {
    $type = $this->entityManager()->getStorage('bat_type_group')->create(array(
      'type' => $type_group_bundle->id(),
    ));

    $form = $this->entityFormBuilder()->getForm($type);

    return $form;
  }

  /**
   * The _title_callback for the type_group.add route.
   *
   * @param \Drupal\bat\TypeGroupBundleInterface $type_group_bundle
   *   The current type group bundle.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(TypeGroupBundleInterface $type_group_bundle) {
    return $this->t('Create @name', array('@name' => $type_group_bundle->label()));
  }

}
