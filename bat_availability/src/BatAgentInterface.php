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
   * @param array
   */
  public function setValidStates(array $availability_states);

  /**
   * @param array
   *
   * @return
   */
  public function getAvailability(array $availability_filters);

  /**
   *
   */
  public function checkAvailability();

  /**
   * @param \DateTime
   * @param \DateTime
   */
  public function updateAvailabilityStates(\DateTime $start_date, \DateTime $end_date, $state);

  /**
   * @param Drupal\bat\Entity\AvailabilityEvent
   */
  public function updateAvailabilityEvents(Drupal\bat\Entity\AvailabilityEvent $availability_event_entity);
}
