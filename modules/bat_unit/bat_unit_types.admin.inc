<?php

/**
 * @file
 * UI for creating unit type entities and controlling Units.
 */

/**
 * Allow manipulation of unit type entities.
 */
function bat_unit_type_list() {
  $unit_add_form = drupal_get_form('unit_type_add_select');
  $content = drupal_render($unit_add_form);
  $content .= unit_type_list_render();
  return $content;
}

/**
 * Unit add form.
 */
function unit_type_add_select($form, &$form_state) {
  // Get unit types.
  $unit_type_bundles = bat_unit_type_bundle_ids();

  $form = array();

  $form['container'] = array(
    '#type' => 'container',
    '#attributes' => array(
      'class' => array('container-inline'),
    ),
  );

  $form['container']['unit_type'] = array(
    '#prefix' => l('', 'admin/bat/unit-type-add'),
    '#type' => 'select',
    '#options' => $unit_type_bundles,
    '#title' => 'Add Unit Type',
  );

  $form['container']['units'] = array(
    '#type' => 'textfield',
    '#title' => 'Units',
    '#size' => 1,
  );

  $form['container']['max'] = array(
    '#type' => 'textfield',
    '#title' => 'Maximum Occupancy',
    '#size' => 1,
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Add',
  );

  return $form;
}

/**
 * Validation handler for unit add form.
 */
function unit_type_add_select_validate($form, &$form_state) {
  if (empty($form_state['values']['quantity'])) {
    form_set_error('quantity', t('Please enter the number of units to add.'));
  } elseif (!is_numeric($form_state['values']['quantity'])) {
    form_set_error('quantity', t('Please enter a valid number of units to add.'));
  }

  if (empty($form_state['values']['max'])) {
    form_set_error('quantity', t('Please enter the maximum occupancy.'));
  } elseif (!is_numeric($form_state['values']['max'])) {
    form_set_error('quantity', t('Please enter a valid occupancy.'));
  }
}

/**
 * Submit handler for unit add form.
 */
function unit_type_add_select_submit($form, &$form_state) {
  global $user;

  // Sanity check.
  if (! $unit_type = bat_unit_type_load($form_state['values']['unit_type'])) {
    drupal_set_message(t('Could not load unit type!'), 'warning');
    return;
  }

  // Create units.
  for ($i = 1; $i <= $form_state['values']['quantity']; $i++) {
    $unit = bat_unit_create(array('type' => $unit_type->type));
    $unit->name = $unit_type->label;
    $unit->max_sleeps = $form_state['values']['max'];
    $unit->created = !empty($unit->date) ? strtotime($unit->date) : REQUEST_TIME;

    // Bookable by default.
    $unit->default_state = 1;
    $unit->uid = $user->uid;
    $unit->save();
  }
}


/**
 * Generate list of unit types.
 */
function unit_type_list_render() {
  // Table setup.
  $header = array('Unit Type', 'Units');

  // Populate table rows.
  $rows = array();
  foreach (bat_unit_type_ids() as $unit_type_id => $unit_type) {
    $unit_type = bat_unit_type_load($unit_type_id);
    $rows[] = array($unit_type->name);
  }

  // The output.
  $content = '<div class="unit-types">';
  $content .= theme('table', array('header' => $header, 'rows' => $rows ));
  $content .= '</div>';

  return $content;
}
