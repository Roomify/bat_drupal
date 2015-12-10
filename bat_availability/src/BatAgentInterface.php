<?php

/**
 * @file
 * Contains \Drupal\bat_availability\BatAgentInterface.
 */

namespace Drupal\bat_availability;

/**
 *
 */
interface BatAgentInterface {
  /**
   *
   */
  public function setValidStates(array $availability_states);

  /**
   *
   */
  public function getAvailability(array $availability_filters);

  /**
   *
   */
  public function checkAvailability();

  /**
   *
   */
  public function updateAvailabilityStates(\DateTime $start_date, \DateTime $end_date, $state);

  /**
   *
   */
  public function updateAvailabilityEvents(Drupal\bat\Entity\AvailabilityEvent $availability_event_entity);
}
