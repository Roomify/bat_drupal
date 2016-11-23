<?php

/**
 * @file
 * Contains a Views field handler to take care of displaying the correct label
 * for unit bundles.
 */

/**
 *
 */
class bat_type_handler_type_revision_status_field extends views_handler_field {

  /**
   * {@inheritdoc}
   */
  public function construct() {
    parent::construct();

    $this->additional_fields['type_id'] = 'type_id';
    $this->additional_fields['revision_id'] = 'revision_id';
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensure_my_table();
    $this->add_additional_fields();
  }

  /**
   * {@inheritdoc}
   */
  public function render($values) {
    $type_id = $this->get_value($values, 'type_id');
    $revision_id = $this->get_value($values, 'revision_id');

    $type = bat_type_load($type_id);
    $type_revision = bat_type_load_revision($revision_id);

    $is_current = ($revision_id == $type->revision_id);
    $is_pending = ($revision_id > $type->revision_id);

    if ($is_pending) {
      return t('in draft/pending publication');
    }
    elseif ($is_current) {
      return t('current revision (published)');
    }

    return t('archived');
  }

}
