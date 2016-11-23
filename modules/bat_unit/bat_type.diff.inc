<?php

/**
 * @file
 */

/**
 * Create a comparison for the type between versions 'old_revision_id' and 'new_revision_id'.
 *
 * @param object $type
 *   BatType on which to perform comparison
 * @param integer $old_revision_id
 *   Version ID of the old revision.
 * @param integer $new_revision_id
 *   Version ID of the new revision.
 */
function bat_type_compare_revisions($type, $old_revision_id, $new_revision_id) {
  module_load_include('inc', 'diff', 'diff.pages');

  // Attaches the CSS.
  $build['#attached'] = diff_build_attachments();

  $state = 'raw_plain';

  $type_revisions = bat_type_revision_list($type);

  $old_type = bat_type_load_revision($old_revision_id);
  $new_type = bat_type_load_revision($new_revision_id);

  $old_account = user_load($type_revisions[$old_revision_id]->revision_uid);
  $new_account = user_load($type_revisions[$new_revision_id]->revision_uid);

  // Generate table header (date, username, log message).
  $old_header = t('!date by !username', array(
    '!date' => l(format_date($old_type->revision_timestamp), "admin/bat/config/types/manage/$type->type_id/revision/$old_type->revision_id/view", array('absolute' => 1)),
    '!username' => theme('username', array('account' => $old_account)),
  ));
  $new_header = t('!date by !username', array(
    '!date' => l(format_date($new_type->revision_timestamp), "admin/bat/config/types/manage/$type->type_id/revision/$new_type->revision_id/view", array('absolute' => 1)),
    '!username' => theme('username', array('account' => $new_account)),
  ));

  $old_log = $old_type->log != '' ? '<p class="revision-log">' . filter_xss($old_type->log) . '</p>' : '';
  $new_log = $new_type->log != '' ? '<p class="revision-log">' . filter_xss($new_type->log) . '</p>' : '';

  // Generate previous diff/next diff links.
  $next_vid = bat_type_diff_get_next_vid($type_revisions, $new_revision_id);
  if ($next_vid) {
    $next_link = l(t('Next difference >'), 'admin/bat/config/types/manage/' . $type->type_id . '/revisions/view/' . $new_revision_id . '/' . $next_vid, array('absolute' => 1));
  }
  else {
    $next_link = '';
  }
  $prev_vid = bat_type_diff_get_previous_vid($type_revisions, $old_revision_id);
  if ($prev_vid) {
    $prev_link = l(t('< Previous difference'), 'admin/bat/config/types/manage/' . $type->type_id . '/revisions/view/' . $prev_vid . '/' . $old_revision_id, array('absolute' => 1));
  }
  else {
    $prev_link = '';
  }

  $header = _diff_default_header($old_header, $new_header);
  $rows = array();
  if ($old_log || $new_log) {
    $rows['logs'] = array(
      array(
        'data' => $old_log,
        'colspan' => 2,
      ),
      array(
        'data' => $new_log,
        'colspan' => 2,
      ),
    );
  }
  $rows['navigation'] = array(
    array(
      'data' => $prev_link,
      'class' => array('diff-prevlink'),
      'colspan' => 2,
    ),
    array(
      'data' => $next_link,
      'class' => array('diff-nextlink'),
      'colspan' => 2,
    ),
  );

  $rows = array_merge($rows, bat_type_diff_body_rows($old_type, $new_type, $state));

  $build['diff_table'] = array(
    '#theme' => 'table__diff__standard',
    '#header' => $header,
    '#rows' => $rows,
    '#attributes' => array('class' => array('diff')),
    '#colgroups' => _diff_default_cols(),
    '#sticky' => FALSE,
  );

  return $build;
}

/**
 * Get the entry in the revisions list after $vid.
 *
 * @param array $type_revisions
 *   Array of type revision IDs in descending order.
 * @param int $vid
 *   Version ID to look for.
 *
 * @return bool|int
 *   Returns FALSE if $vid is the last entry.
 */
function bat_type_diff_get_next_vid($type_revisions, $vid) {
  $previous = NULL;
  foreach ($type_revisions as $revision) {
    if ($revision->revision_id == $vid) {
      return ($previous ? $previous->revision_id : FALSE);
    }
    $previous = $revision;
  }
  return FALSE;
}

/**
 * Get the entry in the revision list before $vid.
 *
 * @param array $type_revisions
 *   Array of type revision IDs in descending order.
 * @param int $vid
 *   Version ID to look for.
 *
 * @return bool|int
 *   Returns FALSE if $vid is the first entry.
 */
function bat_type_diff_get_previous_vid($type_revisions, $vid) {
  $previous = NULL;
  foreach ($type_revisions as $revision) {
    if ($previous && $previous->revision_id == $vid) {
      return $revision->revision_id;
    }
    $previous = $revision;
  }
  return FALSE;
}

/**
 * Creates an array of rows which represent the difference between types.
 *
 * @param object $old_type
 *   BatType for comparison which will be displayed on the left side.
 * @param object $new_type
 *   BatType for comparison which will be displayed on the right side.
 * @param bool $state
 *   The state to render for the diff.
 */
function bat_type_diff_body_rows($old_type, $new_type, $state = 'raw') {
  $context = array(
    'states' => array($state),
    'view_mode' => 'diff_standard',
  );
  return diff_entity_body_rows('bat_type', $old_type, $new_type, $context);
}
