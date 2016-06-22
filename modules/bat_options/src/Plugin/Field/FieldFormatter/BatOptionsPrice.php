<?php

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
		return array();
	}

}
