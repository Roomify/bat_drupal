<?php

/**
 * @file
 * Contains \Drupal\bat_options\Plugin\Field\FieldFormatter\BatOptionsPrice.
 */

namespace Drupal\bat_options\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * @FieldFormatter(
 *   id = "bat_options_price",
 *   label = @Translation("Bat Options Price"),
 *   field_types = {
 *     "bat_options",
 *   }
 * )
 */
class BatOptionsPrice extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    $currency_symbol = '$';

    foreach ($items as $delta => $item) {
      $price = t('@currency_symbol@amount', [
        '@currency_symbol' => $currency_symbol,
        '@amount' => number_format($item->value, 2, '.', ''),
      ]);

      if ($item->value > 0) {
        $element[$delta] = ['#markup' => "{$item->quantity} x {$item->name} - {$price}"];
      }
      else {
        $element[$delta] = ['#markup' => "{$item->quantity} x {$item->name}"];
      }
    }

    return $element;
  }

}
