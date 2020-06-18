<?php

/**
 * @file
 * This field handler aggregates calendar edit links for a Bat Type
 * under a single field.
 */

namespace Drupal\bat_unit\Plugin\views\field;

use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Url;
use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @ViewsField("bat_type_handler_type_calendars_field")
 */
class BatTypeHandlerTypeCalendarsField extends FieldPluginBase {

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * Constructs a BatTypeHandlerTypeCalendarsField object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PathValidatorInterface $path_validator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->pathValidator = $path_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path.validator')
    );
  }

  public function query() {
  }

  public function render(ResultRow $values) {
    $links = [];

    $type = $this->getEntity($values);
    $type_bundle = bat_type_bundle_load($type->bundle());

    if (is_array($type_bundle->default_event_value_field_ids) && $this->getModuleHandler()->moduleExists('bat_event_ui')) {
      foreach ($type_bundle->default_event_value_field_ids as $event_type => $field) {
        if (!empty($field)) {
          $event_type_path = Url::fromRoute('bat_event_ui.calendar', [
            'unit_type' => $type->id(),
            'event_type' => $event_type,
          ])->toString();

          // Check if user has permission to access $event_type_path.
          if ($url_object = $this->pathValidator->getUrlIfValid($event_type_path)) {
            $route_name = $url_object->getRouteName();

            if (bat_event_get_types($event_type)) {
              $event_type_label = bat_event_get_types($event_type)->label();
              $links[$event_type] = [
                'title' => t('Manage @event_type_label', ['@event_type_label' => $event_type_label]),
                'url' => Url::fromRoute($route_name, ['unit_type' => $type->id(), 'event_type' => $event_type]),
              ];
            }
          }
        }
      }
    }

    if (!empty($links)) {
      return [
        '#type' => 'operations',
        '#links' => $links,
      ];
    }
    else {
      // Hide this field.
      $this->options['exclude'] = TRUE;
    }
  }

}
