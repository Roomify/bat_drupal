<?php

/**
 * @file
 * UI for creating Bat Type entities and controlling Units.
 */

/**
 * Allow manipulation of Bat Type entities.
 */
function bat_type_list() {
  ctools_include('modal');
  ctools_modal_add_js();

  // $unit_add_form = drupal_get_form('bat_type_add_select');
  // $content = drupal_render($unit_add_form);
  $content = l(t('Add Type'), 'admin/bat/config/types/add/room');

  $content .= bat_type_list_render();
  return $content;
}

/**
 * Unit add form.
 */
function bat_type_add_select($form, &$form_state) {
  // Get Bat Types.
  $type_bundles = bat_type_bundle_ids();

  $form = array();

  $form['container'] = array(
    '#type' => 'container',
    '#attributes' => array(
      'class' => array('container-inline'),
    ),
  );

  $form['container']['type'] = array(
    '#prefix' => l('', 'admin/bat/type-add'),
    '#type' => 'select',
    '#options' => $type_bundles,
    '#title' => 'Add Type',
  );

  $form['container']['name'] = array(
    '#type' => 'textfield',
    '#title' => 'Name',
    '#size' => 16,
  );

  $form['container']['units'] = array(
    '#type' => 'textfield',
    '#title' => 'Units',
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
function bat_type_add_select_validate($form, &$form_state) {
  if (empty($form_state['values']['units'])) {
    form_set_error('units', t('Please enter the number of units to add.'));
  } elseif (!is_numeric($form_state['values']['units'])) {
    form_set_error('units', t('Please enter a valid number of units to add.'));
  }
}

/**
 * Submit handler for unit add form.
 */
function bat_type_add_select_submit($form, &$form_state) {
  global $user;

  // Sanity check.
  if (!$type = bat_type_load($form_state['values']['type'])) {
    drupal_set_message(t('Could not load Bat Type!'), 'warning');
    return;
  }

  // Create units.
  for ($i = 1; $i <= $form_state['values']['units']; $i++) {
    $unit = bat_unit_create(array('type' => $type->type));
    $unit->name = $type->label;
    $unit->created = !empty($unit->date) ? strtotime($unit->date) : REQUEST_TIME;

    // Bookable by default.
    $unit->type_id = 1;
    $unit->default_state = 1;
    $unit->uid = $user->uid;
    $unit->save();
  }
}

/**
 * Generate list of Bat Types.
 */
function bat_type_list_render() {
  // Table setup.
  $header = array('Type', 'Units');

  // Populate table rows.
  $rows = array();
  foreach (bat_type_ids() as $type_id => $type) {
    $type = bat_type_load($type_id);
    $units = bat_unit_load_multiple(FALSE, array('type_id' => $type_id));
    $options = array('query' => array('type' => $type_id));
    $rows[] = array(
      // Type Name.
      $type->name,
      // Number of Units associated with this Type.
      count($units) . ' ' . l(t('edit'), 'admin/bat/type-edit', $options),
    );
  }

  // The output.
  $content = '<div class="types">';
  $content .= theme('table', array('header' => $header, 'rows' => $rows ));
  $content .= '</div>';

  return $content;
}

/**
 * Form to add/delete Units for a given Type.
 */
function bat_type_edit($form, &$form_state) {
  if (isset($_GET['type'])) {
    $type = $_GET['type'];
  }
  else {
    die("I can't do that, Dave.");
  }
  $form = array();

  $form['#attached']['js'] = array(
    drupal_get_path('module', 'bat_unit') . '/js/bat_type_edit.js',
  );

  $type_object = bat_type_load($type);
  $total_units = count(bat_unit_load_multiple(FALSE, array('type_id' => $type_id)));
  // $units_booked = bat_get_unit_number_booked_by_type_capacity($type, $max_sleeps);

  // Set up options.
  $options = array(
    '' => '',
    'edit_name' => 'Edit name',
    'add_units' => 'Add Units',
  );

  // Add delete/adjust prices options if units are present.
  if ($total_units > 0) {
    $options['adjust_prices'] = t('Adjust Unit prices');
  }

  // Add delete units option if units are available.
  if ($total_units > 0 && ($total_units > $units_booked)) {
    $options['delete_units'] = t('Delete Units');
  }

  // Add delete type option if no units are booked.
  /*
   * TODO
  $units_booked_by_type = bat_get_unit_number_booked_by_type_capacity($type);
  if ($units_booked_by_type == 0) {
    $options['delete_type'] = t('Delete Type and all Units of this type');
  }
  */

  $form['type'] = array(
    '#type' => 'hidden',
    '#value' => $type,
  );

  $form['operation'] = array(
    '#type' => 'select',
    '#title' => t('Operations'),
    '#description' => t('Please select an operation to perform on these Units'),
    '#options' => $options,
    '#required' => FALSE,
  );

  // Edit name fieldset.
  $form['edit_name'] = array(
    '#type' => 'fieldset',
    '#title' => 'Edit Type name',
  );
  $form['edit_name']['name'] = array(
    '#type' => 'textfield',
    '#default_value' => $type_object->name,
  );
  $form['edit_name']['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Save edits',
    '#validate' => array('bat_unit_edit_type_name_validate'),
    '#submit' => array('bat_unit_edit_type_name_submit'),
  );

  // Add Units fieldset.
  $form['add_units'] = array(
    '#type' => 'fieldset',
    '#title' => 'Add Units',
  );
  $form['add_units']['quantity'] = array(
    '#type' => 'textfield',
    '#default_value' => 1,
  );
  $form['add_units']['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Add Units',
    '#validate' => array('bat_unit_add_units_validate'),
    '#submit' => array('bat_unit_add_units_submit'),
  );

  // Delete type fieldset.
  $form['delete_type'] = array(
    '#type' => 'fieldset',
    '#title' => 'Delete Type',
  );
  $form['delete_type']['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Delete ' . $type_object->label . ' stateroom type',
    '#submit' => array('bat_unit_delete_type_submit'),
  );

  // Delete units fieldset.
  $form['delete_units'] = array(
    '#type' => 'fieldset',
    '#title' => 'Delete ' . $type_object->label . ' Units',
  );
  $form['delete_units']['number'] = array(
    '#type' => 'textfield',
    '#size' => 2,
    '#description' => 'Number of Units to delete - maximum ' . ($total_units - $units_booked),
  );
  $form['delete_units']['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Delete staterooms',
    '#validate' => array('bat_unit_delete_units_validate'),
    '#submit' => array('bat_unit_delete_units_submit'),
  );

  return $form;
}

/**
 * Validation function for Bat Type name editing.
 */
function bat_unit_edit_type_name_validate($form, &$form_state) {
  if (!strlen($form_state['values']['name'])) {
    form_set_error('name', t('Please enter a name for this Type.'));
  }
}

/**
 * Submit function to edit type name and update any units.
 */
function bat_unit_edit_type_name_submit($form, &$form_state) {
  if (isset($form['type']['#value'])) {
    $type = $form['type']['#value'];
    if ($type_object = bat_type_load($type)) {

      // Update unit names.
      $units = bat_unit_load_multiple(FALSE, array('type_id' => $type_id));

      foreach ($units as $unit_id) {
        $unit = bat_unit_load($unit_id);
        $unit->name = str_replace($type_object->label, $form_state['values']['name'], $unit->name);
        bat_unit_save($unit);
      }

      // Update Bat Type name.
      $type_object->name = $form_state['values']['name'];
      bat_type_save($type_object);
    }
  }

  bat_unit_dismiss_modal($form_state, 'admin/bat/types');
}

/**
 * Validation function for adding new units.
 */
function bat_unit_add_units_validate($form, &$form_state) {
  if (!strlen($form_state['values']['quantity'])) {
    form_set_error('quantity', t('Please enter a quantity.'));
  }
  elseif (!is_numeric($form_state['values']['quantity'])) {
    form_set_error('name', t('Please enter a numeric quantity.'));
  }
}

/**
 * Submit function to add new units.
 */
function bat_unit_add_units_submit($form, &$form_state) {
  global $user;
  if (isset($form['type']['#value'])) {
    $type = $form['type']['#value'];
    if ($type_object = bat_type_load($type)) {
      for ($i = 1; $i <= $form_state['values']['quantity']; $i++) {
        $unit = bat_unit_create(array(
          'type_id' => $type,
          'name' => $type_object->name . ' ' . $i,
          'uid' => $user->uid,
          'created' => REQUEST_TIME,
          'type' => 'default',
        ));

        bat_unit_save($unit);
      }
    }
  }

  bat_unit_dismiss_modal($form_state, 'admin/bat/types');
}

/**
 * Helper function - Dismiss modal after form is complete.
 */
function bat_unit_dismiss_modal($form_state, $path) {
  if ($form_state['submitted']) {
    $commands[] = ctools_modal_command_dismiss();
    $commands[] = ctools_ajax_command_redirect($path);
    module_load_include('inc', 'ctools', 'includes/modal');
    module_load_include('inc', 'ctools', 'includes/ajax');
    print ajax_render($commands);
    exit;
  }
}
