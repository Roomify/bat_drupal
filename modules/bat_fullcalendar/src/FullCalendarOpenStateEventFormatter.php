<?php

/**
 * @file
 * Class FullCalendarOpenStateEventFormatter
 */

namespace Drupal\bat_fullcalendar;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\bat_event\EventTypeInterface;
use Roomify\Bat\Event\Event;
use Roomify\Bat\Event\EventInterface;
use Roomify\Bat\EventFormatter\AbstractEventFormatter;

/**
 *
 */
class FullCalendarOpenStateEventFormatter extends AbstractEventFormatter {

  /**
   * The event type.
   *
   * @var \Drupal\bat_event\EventTypeInterface
   */
  protected $eventType;

  /**
   * Print as background event.
   *
   * @var bool
   */
  protected $background;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(AccountInterface $current_user, ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, EntityTypeManagerInterface $entity_manager) {
    $this->background = TRUE;
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * @param \Drupal\bat_event\EventTypeInterface $event_type
   *   The event type.
   */
  public function setEventType(EventTypeInterface $event_type) {
    $this->eventType = $event_type;
  }

  /**
   * @param bool $background
   *   The event type.
   */
  public function setBackground($background) {
    $this->background = $background;
  }

  /**
   * {@inheritdoc}
   */
  public function format(EventInterface $event) {
    $config = $this->configFactory->get('bat_fullcalendar.settings');

    $editable = FALSE;

    // Load the target entity from Drupal.
    $target_entity = $this->entityTypeManager->getStorage($this->eventType->getTargetEntityType())->load($event->getUnitId());

    // Get the target entity default value.
    $default_value = $target_entity->getEventDefaultValue($this->eventType->id());

    if ($event->getValue()) {
      $bat_event = bat_event_load($event->getValue());

      // Change the default value to the one that the event actually stores in the entity.
      $default_value = $bat_event->getEventValue();

      if (bat_event_access($bat_event, 'update', $this->currentUser)->isAllowed()) {
        $editable = TRUE;
      }
    }

    $formatted_event = [
      'start' => $event->startYear() . '-' . $event->startMonth('m') . '-' . $event->startDay('d') . 'T' . $event->startHour('H') . ':' . $event->startMinute() . ':00',
      'end' => $event->endYear() . '-' . $event->endMonth('m') . '-' . $event->endDay('d') . 'T' . $event->endHour('H') . ':' . $event->endMinute() . ':00',
      'title' => $target_entity->formatEventValue($this->eventType->id(), $default_value),
      'blocking' => 0,
      'fixed' => 0,
      'editable' => $editable,
    ];

    if ($event->getValue() == 0) {
      $formatted_event['color'] = $config->get('bat_open_state_default_zero_color');
    }
    else {
      $formatted_event['color'] = $config->get('bat_open_state_default_color');
    }

    if ($this->background) {
      $formatted_event['rendering'] = 'background';
    }

    $formatted_event['type'] = $this->eventType->id();

    // Allow other modules to alter the event data.
    $this->moduleHandler->alter('bat_fullcalendar_formatted_event', $formatted_event);

    return $formatted_event;
  }

}
