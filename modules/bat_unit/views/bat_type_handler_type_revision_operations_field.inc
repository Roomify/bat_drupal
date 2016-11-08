<?php

/**
 * @file
 * This field handler aggregates operations that can be done on a type
 * under a single field providing a more flexible way to present them in a view.
 */

class bat_type_handler_type_revision_operations_field extends views_handler_field {

  function construct() {
    parent::construct();

    $this->additional_fields['type_id'] = 'type_id';
    $this->additional_fields['revision_id'] = 'revision_id';
  }

  function query() {
    $this->ensure_my_table();
    $this->add_additional_fields();
  }

  function render($values) {
    $type_id = $this->get_value($values, 'type_id');
    $revision_id = $this->get_value($values, 'revision_id');

    $type = entity_revision_load('bat_type', $revision_id);
    if (entity_revision_is_default('bat_type', $type)) {
      return '<b>' . t('This is the published revision.') . '</b>';
    }
    else {
      $links = array();

      if (bat_type_access_revision('view', $type, $revision_id)) {
        $links[] = array(
          'href'  => 'admin/bat/config/types/type/' . $type_id . '/revisions/' . $revision_id . '/view',
          'title' => t('View'),
        );
      }

      if (bat_type_access_revision('revert', $type, $revision_id)) {
        $links[] = array(
          'href'  => 'admin/bat/config/types/type/' . $type_id . '/revisions/' . $revision_id . '/revert',
          'title' => t('Revert'),
        );
      }

      if (bat_type_access_revision('delete', $type, $revision_id)) {
        $links[] = array(
          'href'  => 'admin/bat/config/types/type/' . $type_id . '/revisions/' . $revision_id . '/delete',
          'title' => t('Delete'),
        );
      }

      if (!empty($links)) {
        return theme('links', array(
          'links' => $links,
          'attributes' => array(
            'class' => array(
              'links',
              'inline',
              'operations',
            ),
          ),
        ));
      }
    }
  }

}
