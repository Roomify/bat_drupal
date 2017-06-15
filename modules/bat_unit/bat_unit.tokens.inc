<?php

/**
 * @file
 * Builds placeholder replacement tokens for unit-related data.
 */

/**
 * Implements hook_token_info().
 */
function bat_unit_token_info() {
  $bat_type = array(
    'name' => t('BAT Type', array(), array('context' => 'a bat type')),
    'description' => t('Tokens related to bat type.'),
    'needs-data' => 'bat-type',
  );

  $bat_unit = array(
    'name' => t('BAT Unit', array(), array('context' => 'a bat unit')),
    'description' => t('Tokens related to bat unit.'),
    'needs-data' => 'bat-unit',
  );

  $info['author'] = array(
    'name' => t('Author'),
    'description' => t('The author.'),
    'type' => 'user',
  );

  return array(
    'types' => array('bat_type' => $bat_type, 'bat_unit' => $bat_unit),
    'tokens' => array('bat_type' => $info, 'bat_unit' => $info),
  );
}

/**
 * Implements hook_tokens().
 */
function bat_unit_tokens($type, $tokens, array $data = array(), array $options = array()) {
  $replacements = array();

  if ($type == 'bat_type' && !empty($data['bat_type'])) {
    $bat_type = $data['bat_type'];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'author':
          $account = user_load($bat_type->uid);
          $name = format_username($account);
          $replacements[$original] = $sanitize ? check_plain($name) : $name;
          break;
      }
    }

    if ($author_tokens = token_find_with_prefix($tokens, 'author')) {
      $author = user_load($bat_type->uid);
      $replacements += token_generate('user', $author_tokens, array('user' => $author), $options);
    }
  }

  if ($type == 'bat_unit' && !empty($data['bat_unit'])) {
    $bat_unit = $data['bat_unit'];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'author':
          $account = user_load($bat_unit->uid);
          $name = format_username($account);
          $replacements[$original] = $sanitize ? check_plain($name) : $name;
          break;
      }
    }

    if ($author_tokens = token_find_with_prefix($tokens, 'author')) {
      $author = user_load($bat_unit->uid);
      $replacements += token_generate('user', $author_tokens, array('user' => $author), $options);
    }
  }

  return $replacements;
}
