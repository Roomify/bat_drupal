<?php

namespace Drupal\bat_unit\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a unit operations bulk form element.
 *
 * @ViewsField("unit_bulk_form")
 */
class UnitBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No units selected.');
  }

}
