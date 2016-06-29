<?php

namespace Drupal\bat_fullcalendar\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Determines access to for event add pages.
 */
class EventManagementAccessCheck implements AccessInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a EntityCreateAccessCheck object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * Checks access to the event add page for the event type.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   *
   * @return string
   *   A \Drupal\Core\Access\AccessInterface constant value.
   */
  public function access(AccountInterface $account, $entity_id, $event_type, $event_id, $start_date, $end_date) {
  	if ($event_id == 0) {
	    return bat_event_access(bat_event_create(array('type' => $event_type)), 'create', \Drupal::currentUser());
	  }
	  else {
	    $event = bat_event_load($event_id);
	    return bat_event_access($event, 'update', \Drupal::currentUser());
	  }
  }

}
