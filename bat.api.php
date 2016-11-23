<?php

/**
 * @file
 * This file contains no working PHP code; it exists to provide additional
 * documentation for doxygen as well as to document hooks in the standard
 * Drupal manner.
 */

/**
 * Allows modules to deny or provide access for a user to perform a non-view
 * operation on an entity before any other access check occurs.
 *
 * Modules implementing this hook can return FALSE to provide a blanket
 * prevention for the user to perform the requested operation on the specified
 * entity. If no modules implementing this hook return FALSE but at least one
 * returns TRUE, then the operation will be allowed, even for a user without
 * role based permission to perform the operation.
 *
 * If no modules return FALSE but none return TRUE either, normal permission
 * based checking will apply.
 *
 * @param string $op
 *   The request operation: update, create, or delete.
 * @param object $entity
 *   The entity to perform the operation on.
 * @param object $account
 *   The user account whose access should be determined.
 * @param string $entity_type
 *   The machine-name of the entity type of the given $entity.
 *
 * @return bool
 *   TRUE or FALSE indicating an explicit denial of permission or a grant in the
 *   presence of no other denials; NULL to not affect the access check at all.
 */
function hook_bat_entity_access($op, $entity, $account, $entity_type) {
  // No example.
}
