<?php

/**
 * @file
 * Contains \Drupal\bat_availability\BatEventInterface.
 */

namespace Drupal\bat_availability;

/**
 *
 */
interface BatEventInterface {
	/**
   * @return \DateTime
   */
  public function getStartDate();

  /**
   * @return \DateTime
   */
  public function getEndDate();

  /**
   * @param \DateTime
   */
  public function setStartDate(\DateTime $start_date);

  /**
   * @param \DateTime
   */
  public function setEndDate(\DateTime $end_date);

  /**
   * @return string
   */
  public function getState();

  /**
   * @param string
   */
  public function setState($state);

  /**
   * @return int
   */
  public function getStateInteger();

  /**
   * @return int
   */
  public function getEventId();
}
