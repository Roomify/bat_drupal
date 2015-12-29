<?php

/**
 * @file
 * Class EventFormatter
 */

namespace Drupal\bat_event;

use Drupal\bat\Event;
use Drupal\bat_event\EventStyle;

class EventFormatter {

  public $event;

  public function __construct(Event $event) {
    $this->event = $event;
  }

  public function formatJson($event_type) {
    ctools_include('plugins');
    $formatters = ctools_get_plugins('bat_event', 'bat_eventstyle');

    foreach ($formatters as $formatter) {
      $class = ctools_plugin_get_class($formatter, 'handler');
      $object_formatter = new $class($this->event);

      $event = $object_formatter->format($event_type);
    }

    return $event;
  }

}
