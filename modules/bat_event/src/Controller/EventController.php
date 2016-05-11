<?php

namespace Drupal\bat_event\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\bat_event\EventTypeInterface;

/**
 * Returns responses for Type routes.
 */
class EventController extends ControllerBase implements ContainerInjectionInterface {

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
   * Constructs a TypeController object.
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
      '#theme' => 'bat_event_add_list',
      '#cache' => [
        'tags' => $this->entityManager()->getDefinition('type_bundle')->getListCacheTags(),
      ],
    ];

    $content = array();

    // Only use node types the user has access to.
    foreach ($this->entityManager()->getStorage('event_type')->loadMultiple() as $type) {
      $access = $this->entityManager()->getAccessControlHandler('event')->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }
    }

    // Bypass the node/add listing if only one content type is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('entity.event.add_form', array('type_bundle' => $type->id()));
    }

    $build['#content'] = $content;

    return $build;
  }

  /**
   * Provides the node submission form.
   *
   * @param \Drupal\bat_event\EventTypeInterface $event_type
   *   The node type entity for the node.
   *
   * @return array
   *   A node submission form.
   */
  public function add(EventTypeInterface $event_type) {
    $type = $this->entityManager()->getStorage('event')->create(array(
      'type' => $event_type->id(),
    ));

    $form = $this->entityFormBuilder()->getForm($type);

    return $form;
  }

  /**
   * The _title_callback for the node.add route.
   *
   * @param \Drupal\bat_event\EventTypeInterface $event_type
   *   The current node.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(EventTypeInterface $event_type) {
    return $this->t('Create @name', array('@name' => $event_type->label()));
  }

}
