<?php

/**
 * @file
 * Bat Facet API definitions.
 */

/**
 * Implements hook_facetapi_facet_info().
 */
function bat_facets_facetapi_facet_info(array $searcher_info) {
  $facets = array();

  // Facets are usually associated with the type of content stored in the index.
  if (isset($searcher_info['types']['bat_type'])) {

    $facets['bat_state'] = array(
      'name' => 'bat_state',
      'label' => t('Bat State'),
      'description' => t('Bat Availability State facet'),
      'field' => 'bat_state',
      'field alias' => 'bat_state',
      'field api name' => FALSE,
      'field api bundles' => array(),
      'query types' => array('term'),
      'dependency plugins' => array('role'),
      'default widget' => 'bat_state',
      'allowed operators' => array(FACETAPI_OPERATOR_AND => TRUE, FACETAPI_OPERATOR_OR => FALSE),
      'facet missing allowed' => FALSE,
      'facet mincount allowed' => FALSE,
      'weight' => 0,
      'map callback' => FALSE,
      'map options' => array(),
      'hierarchy callback' => FALSE,
      'values callback' => FALSE,
      'min callback' => FALSE,
      'max callback' => FALSE,
    );
  }

  return $facets;
}

/**
 * Implements hook_facetapi_widgets().
 */
function bat_facets_facetapi_widgets() {
  return array(
    'bat_state' => array(
      'handler' => array(
        'label' => t('Bat State'),
        'class' => 'BatStateWidget',
        'query types' => array('term'),
      ),
    ),
  );
}
