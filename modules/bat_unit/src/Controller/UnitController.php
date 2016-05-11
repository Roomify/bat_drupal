<?php

namespace Drupal\bat_unit\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
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
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a UnitController object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct() {
    //$this->dateFormatter = $date_formatter;
    //$this->renderer = $renderer;
  }

  /**
   * Displays add content links for available content types.
   *
   * Redirects to node/add/[type] if only one content type is available.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A render array for a list of the node types that can be added; however,
   *   if there is only one node type defined for the site, the function
   *   will return a RedirectResponse to the node add page for that one node
   *   type.
   */
  public function addPage() {
    $build = [
      '#theme' => 'bat_unit_add_list',
      '#cache' => [
        'tags' => $this->entityManager()->getDefinition('unit_bundle')->getListCacheTags(),
      ],
    ];

    $content = array();

    // Only use node types the user has access to.
    foreach ($this->entityManager()->getStorage('unit_bundle')->loadMultiple() as $type) {
      $access = $this->entityManager()->getAccessControlHandler('unit')->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }
      //$this->renderer->addCacheableDependency($build, $access);
    }

    // Bypass the node/add listing if only one content type is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('entity.unit.add_form', array('unit_bundle' => $type->id()));
    }

    $build['#content'] = $content;

    return $build;
  }

  /**
   * Provides the node submission form.
   *
   * @param \Drupal\bat_unit\UnitBundleInterface $unit_bundle
   *   The node type entity for the node.
   *
   * @return array
   *   A node submission form.
   */
  public function add(UnitBundleInterface $unit_bundle) {
    $unit = $this->entityManager()->getStorage('unit')->create(array(
      'type' => $unit_bundle->id(),
    ));

    $form = $this->entityFormBuilder()->getForm($unit);

    return $form;
  }

  /**
   * The _title_callback for the node.add route.
   *
   * @param \Drupal\bat_unit\UnitBundleInterface $unit_bundle
   *   The current node.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(UnitBundleInterface $unit_bundle) {
    return $this->t('Create @name', array('@name' => $unit_bundle->label()));
  }

}
