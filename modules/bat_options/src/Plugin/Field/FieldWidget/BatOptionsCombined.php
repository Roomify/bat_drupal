<?php

namespace Drupal\bat_options\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @FieldWidget(
 *   id = "bat_options_combined",
 *   label = @Translation("Combined text field'"),
 *   field_types = {
 *     "bat_options"
 *   }
 * )
 */
class BatOptionsCombined extends WidgetBase {

	/**
	 * {@inheritdoc}
	 */
	public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
	}

}
