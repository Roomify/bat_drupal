<?php

/**
 * @file
 * Class AbstractEventStyle
 */

namespace Drupal\bat_event;

use Roomify\Bat\Event\Event;
use Drupal\bat_event\EventStyle;

abstract class AbstractEventStyle implements EventStyle {

  /**
   *
   */
  public $event;

  /**
   *
   */
  public function __construct(Event $event) {
    $this->event = $event;
  }

}
