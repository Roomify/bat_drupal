<?php

/**
 * @file
 */

/**
 * Implements hook_rules_event_info().
 */
function bat_unit_rules_event_info() {
  $default = array(
    'group' => t('Type Revisioning'),
    'variables' => _bat_unit_rules_event_variables(),
  );
  $events = array(
    'bat_type_revisioning_post_update' => $default + array(
      'label' => t('Type revision has been updated'),
    ),
    'bat_type_revisioning_pre_publish' => $default + array(
      'label' => t('Type revision is going to be published'),
    ),
    'bat_type_revisioning_post_publish' => $default + array(
      'label' => t('Type revision has been published'),
    ),
    'bat_type_revisioning_pre_revert' => $default + array(
      'label' => t('Type is going to be reverted to revision'),
    ),
    'bat_type_revisioning_post_revert' => $default + array(
      'label' => t('Type has been reverted to revision'),
    ),
    'bat_type_revisioning_post_unpublish' => $default + array(
      'label' => t('Type has been unpublished'),
    ),
    'bat_type_revisioning_pre_delete' => $default + array(
      'label' => t('Type revision is going to be deleted'),
    ),
    'bat_type_revisioning_post_delete' => $default + array(
      'label' => t('Type revision has been deleted'),
    ),
  );
  return $events;
}

/**
 * Returns event variables suitable for use with a type revision event.
 */
function _bat_unit_rules_event_variables() {
  $vars = array(
    'user' => array(
      'type' => 'user',
      'label' => t('acting user'),
      'description' => t('The acting user.'),
      'handler' => 'bat_type_revisioning_events_argument_global_user',
    ),
    'bat_type' => array(
      'type' => 'bat_type',
      'label' => t('target type.'),
    ),
    'revision' => array(
      'type' => 'bat_type',
      'label' => t('current revision of target content.'),
      'description' => t('The current content revision'),
      'handler' => 'bat_type_revisioning_events_argument_current_revision',
    ),
  );
  return $vars;
}

/**
 * Get global user argument.
 */
function bat_type_revisioning_events_argument_global_user($arguments, $name, $info) {
  global $user;
  return user_load($user->uid);
}

/**
 * Evaluate revision argument.
 */
function bat_type_revisioning_events_argument_current_revision($arguments, $name, $info) {
  if (empty($arguments['bat_type'])) {
    drupal_set_message(t('Revisioning: could not evaluate rule condition -- type variable missing.'), 'warning');
    return FALSE;
  }
  $type = $arguments['bat_type'];
  $current_vid = $type->current_revision_id;
  if ($type->revision_id != $current_vid) {
    $current = bat_type_load($type->type_id, $current_vid);
    return $current;
  }
  return $type;
}

/**
 * Implements hook_rules_action_info().
 */
function bat_unit_rules_action_info() {
  $default = array(
    'group' => t('Type Revisioning'),
  );
  return array(
    'bat_type_revisioning_rules_action_publish_latest' => $default + array(
      'label' => t('Publish the most recent pending revision'),
      'parameter' => array(
        'bat_type' => array('type' => 'bat_type', 'label' => t('type')),
      ),
    ),
    'bat_type_revisioning_rules_action_load_current' => $default + array(
      'label' => t('Load current revision of type'),
      'parameter' => array(
        'bat_type' => array('type' => 'bat_type', 'label' => t('type')),
      ),
      'new variables' => array(
        'loaded_current_revision' => array(
          'type' => 'bat_type',
          'label' => t('Loaded current revision of type'),
          'save' => FALSE,
          'label callback' => 'revisioning_rules_loaded_current_label',
        ),
      ),
    ),
  );
}

/**
 * Action: load current revision of provided type.
 */
function bat_type_revisioning_rules_action_load_current($type) {
  $current_vid = $type->current_revision_id;
  if ($type->revision_id != $current_vid) {
    $current = bat_type_load_revision($current_vid);
    return array('loaded_current_revision' => $current);
  }
  return array('loaded_current_revision' => $type);
}

/**
 * Action: publish most recent pending revision.
 */
function bat_type_revisioning_rules_action_publish_latest($type) {
  // Get the latest pending revision.
  $pending_revisions = bat_type_get_pending_revisions($type->type_id);
  $latest_pending = array_shift($pending_revisions);
  if ($latest_pending) {
    $type_revision = bat_type_load_revision($latest_pending->revision_id);

    bat_type_revisioning_event('pre publish', $type_revision);

    $type_revision->default_revision = TRUE;
    $type_revision->save();

    bat_type_revisioning_event('post publish', $type_revision);

    return TRUE;
  }
  // If there is no pending revision, take the current revision, provided it is
  // NOT published.
  if (!$type->status) {
    if (!isset($type->is_current)) {
      $type->current_revision_id = bat_type_get_current_type_revision_id($type->type_id);
      $type->is_current = revisioning_revision_is_current($type);
    }
    if ($type->is_current) {
      bat_type_revisioning_event('pre publish', $type_revision);

      $type_revision->default_revision = TRUE;
      $type_revision->save();

      bat_type_revisioning_event('post publish', $type_revision);

      return TRUE;
    }
  }
  return FALSE;
}
