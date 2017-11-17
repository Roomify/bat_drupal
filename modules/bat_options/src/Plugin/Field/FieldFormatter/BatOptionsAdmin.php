<?php

/**
 * @file
 * Contains \Drupal\bat_options\Plugin\Field\FieldFormatter\BatOptionsAdmin.
 */

namespace Drupal\bat_options\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * @FieldFormatter(
 *   id = "bat_options_admin",
 *   label = @Translation("Bat Options Administrator"),
 *   field_types = {
 *     "bat_options",
 *   }
 * )
 */
class BatOptionsAdmin extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      if ($item->operation == 'no_charge') {
        $element[$delta] = ['#markup' => "{$item->quantity} x {$item->name} - no charge"];
      }
      else {
        $element[$delta] = ['#markup' => "{$item->quantity} x {$item->name} - {$item->operation} {$item->value}"];
      }
    }

    return $element;
  }

}
