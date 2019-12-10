<?php

/**
 * @file
 */

namespace Drupal\bat_event\Plugin\views\field;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @ViewsField("bat_event_handler_duration_field")
 */
class BatEventHandlerDurationField extends FieldPluginBase {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a TimeInterval plugin object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DateFormatterInterface $date_formatter) {
    $this->dateFormatter = $date_formatter;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('date.formatter')
    );
  }

  public function query() {
  }

  public function render(ResultRow $values) {
    $event = $this->getEntity($values);

    $value = $event->getEndDate()->getTimestamp() - $event->getStartDate()->getTimestamp();

    return $this->sanitizeValue($this->dateFormatter->formatInterval($value));
  }

}
