<?php

/**
 * @file
 * Interface EventStyle
 */

namespace Drupal\bat_event;

interface EventStyle {

  /**
   * @param $event_type
   */
  public function format($event_type);

}
