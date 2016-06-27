<?php

namespace Drupal\bat\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\bat\TypeGroupBundleInterface;

/**
 * Returns responses for Type Group routes.
 */
class TypeGroupController extends ControllerBase implements ContainerInjectionInterface {

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
   * Constructs a TypeGroupController object.
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
      '#theme' => 'bat_type_group_add_list',
      '#cache' => [
        'tags' => $this->entityManager()->getDefinition('bat_type_group_bundle')->getListCacheTags(),
      ],
    ];

    $content = array();

    // Only use node types the user has access to.
    foreach ($this->entityManager()->getStorage('bat_type_group_bundle')->loadMultiple() as $type) {
      $access = $this->entityManager()->getAccessControlHandler('bat_type_group')->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }
    }

    // Bypass the node/add listing if only one content type is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('entity.bat_type_group.add_form', array('property_type' => $type->id()));
    }

    $build['#content'] = $content;

    return $build;
  }

  /**
   * Provides the node submission form.
   *
   * @param \Drupal\bat\TypeGroupBundleInterface $property_type
   *   The node type entity for the node.
   *
   * @return array
   *   A node submission form.
   */
  public function add(TypeGroupBundleInterface $property_type) {
    $type = $this->entityManager()->getStorage('bat_type_group')->create(array(
      'type' => $property_type->id(),
    ));

    $form = $this->entityFormBuilder()->getForm($type);

    return $form;
  }

  /**
   * The _title_callback for the node.add route.
   *
   * @param \Drupal\bat\TypeGroupBundleInterface $property_type
   *   The current node.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(TypeGroupBundleInterface $property_type) {
    return $this->t('Create @name', array('@name' => $property_type->label()));
  }

}
