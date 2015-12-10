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
   *
   */
  public function getStartDate();

  /**
   *
   */
  public function getEndDate();

  /**
   *
   */
  public function setStartDate(\DateTime $start_date);

  /**
   *
   */
  public function setEndDate(\DateTime $end_date);

  /**
   *
   */
  public function getState();

  /**
   *
   */
  public function setState($state);

  /**
   *
   */
  public function getStateInteger();

  /**
   *
   */
  public function getEventId();
}
