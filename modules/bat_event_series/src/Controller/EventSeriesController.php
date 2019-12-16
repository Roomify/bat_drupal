<?php

/**
 * @file
 * Contains \Drupal\bat_event_series\Controller\EventSeriesController.
 */

namespace Drupal\bat_event_series\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\bat_event\EventInterface;
use Drupal\bat_event_series\EventSeriesInterface;
use Drupal\bat_event_series\EventSeriesTypeInterface;

/**
 * Returns responses for Type routes.
 */
class EventSeriesController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Constructs a TypeController object.
   */
  public function __construct() {
  }

  /**
   * Displays add event links for available event series types.
   *
   * Redirects to admin/bat/events/event_series/add[type] if only one event series type is available.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A render array for a list of the event series types that can be added; however,
   *   if there is only one event series type defined for the site, the function
   *   will return a RedirectResponse to the event add page for that one event
   *   type.
   */
  public function addPage() {
    $build = [
      '#theme' => 'bat_event_series_add_list',
      '#cache' => [
        'tags' => $this->entityTypeManager()->getDefinition('bat_event_series_type')->getListCacheTags(),
      ],
    ];

    $content = [];

    // Only use event series types the user has access to.
    foreach ($this->entityTypeManager()->getStorage('bat_event_series_type')->loadMultiple() as $type) {
      $access = $this->entityTypeManager()->getAccessControlHandler('bat_event_series')->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }
    }

    // Bypass the add listing if only one event series type is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('entity.bat_event_series.add_form', ['event_series_type' => $type->id()]);
    }

    $build['#content'] = $content;

    return $build;
  }

  /**
   * Provides the event submission form.
   *
   * @param \Drupal\bat_event_series\EventSeriesTypeInterface $event_series_type
   *   The event series type entity for the event.
   *
   * @return array
   *   An event submission form.
   */
  public function add(EventSeriesTypeInterface $event_series_type) {
    $type = $this->entityTypeManager()->getStorage('bat_event_series')->create([
      'type' => $event_series_type->id(),
    ]);

    $form = $this->entityFormBuilder()->getForm($type);

    return $form;
  }

  /**
   * The _title_callback for the event.add route.
   *
   * @param \Drupal\bat_event_series\EventSeriesTypeInterface $event_series_type
   *   The current event series type.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(EventSeriesTypeInterface $event_series_type) {
    return $this->t('Create @name', ['@name' => $event_series_type->label()]);
  }

}
