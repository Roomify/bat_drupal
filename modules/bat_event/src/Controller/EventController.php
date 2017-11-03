<?php

/**
 * @file
 * Contains \Drupal\bat_event\Controller\EventController.
 */

namespace Drupal\bat_event\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\bat_event\EventInterface;
use Drupal\bat_event\EventTypeInterface;

/**
 * Returns responses for Type routes.
 */
class EventController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Constructs a TypeController object.
   */
  public function __construct() {
  }

  /**
   * Displays add event links for available event types.
   *
   * Redirects to admin/bat/events/event/add/[type] if only one event type is available.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A render array for a list of the event types that can be added; however,
   *   if there is only one event type defined for the site, the function
   *   will return a RedirectResponse to the event add page for that one event
   *   type.
   */
  public function addPage() {
    $build = [
      '#theme' => 'bat_event_add_list',
      '#cache' => [
        'tags' => $this->entityTypeManager()->getDefinition('bat_type_bundle')->getListCacheTags(),
      ],
    ];

    $content = [];

    // Only use event types the user has access to.
    foreach ($this->entityTypeManager()->getStorage('bat_event_type')->loadMultiple() as $type) {
      $access = $this->entityTypeManager()->getAccessControlHandler('bat_event')->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }
    }

    // Bypass the add listing if only one event type is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('entity.bat_event.add_form', ['event_type' => $type->id()]);
    }

    $build['#content'] = $content;

    return $build;
  }

  /**
   * Provides the event submission form.
   *
   * @param \Drupal\bat_event\EventTypeInterface $event_type
   *   The event type entity for the event.
   *
   * @return array
   *   An event submission form.
   */
  public function add(EventTypeInterface $event_type) {
    $type = $this->entityTypeManager()->getStorage('bat_event')->create([
      'type' => $event_type->id(),
    ]);

    $form = $this->entityFormBuilder()->getForm($type);

    return $form;
  }

  /**
   * The _title_callback for the event.add route.
   *
   * @param \Drupal\bat_event\EventTypeInterface $event_type
   *   The current event type.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(EventTypeInterface $event_type) {
    return $this->t('Create @name', ['@name' => $event_type->label()]);
  }

  /**
   * Provides the event edit form.
   *
   * @param \Drupal\bat_event\EventInterface $event
   *   The event event for edit.
   *
   * @return array
   *   An event edit form.
   */
  public function editEvent(EventInterface $event) {
    $input = \Drupal::request()->request->all();
    $programmed = isset($input['form_id']);
    $input['form_id'] = 'bat_event_' . $event->bundle() . '_edit_form';

    $form = $this->entityFormBuilder()->getForm($event, 'default', ['programmed' => $programmed, 'input' => $input]);

    return $form;
  }

}
