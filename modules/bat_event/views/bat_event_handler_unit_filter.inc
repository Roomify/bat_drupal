<?php

/**
 * @file
 */

class bat_event_handler_unit_filter extends views_handler_filter_many_to_one {
  function init(&$view, &$options) {
    parent::init($view, $options);
  }

  function has_extra_options() { return TRUE; }

  function get_value_options() { /* don't overwrite the value options */ }

  function option_definition() {
    $options = parent::option_definition();

    $options['type'] = array('default' => 'textfield');

    return $options;
  }

  function extra_options_form(&$form, &$form_state) {
    $form['type'] = array(
      '#type' => 'radios',
      '#title' => t('Selection type'),
      '#options' => array('select' => t('Dropdown'), 'textfield' => t('Autocomplete')),
      '#default_value' => $this->options['type'],
    );
  }

  function value_form(&$form, &$form_state) {
    if ($this->options['type'] == 'textfield') {
      $default = '';
      if ($this->value) {
        $result = bat_unit_load_multiple($this->value);
        foreach ($result as $entity) {
          if ($default) {
            $default .= ', ';
          }
          $default .= entity_label('bat_unit', $entity);
        }
      }

      $form['value'] = array(
        '#title' => t('Select units'),
        '#type' => 'textfield',
        '#default_value' => $default,
        '#autocomplete_path' => 'admin/views/ajax/autocomplete/bat_unit',
      );
    }
    else {
      $options = array();
      $query = db_select('bat_units', 't');
      $query->fields('t');
      $query->orderby('t.name');
      $result = $query->execute();

      $unit_ids = array();
      foreach ($result as $unit) {
        $unit_ids[] = $unit->unit_id;
      }
      $entities = bat_unit_load_multiple($unit_ids);
      foreach ($entities as $entity) {
        $options[$entity->unit_id] = entity_label('bat_unit', $entity);
      }

      $default_value = (array) $this->value;

      if (!empty($form_state['exposed'])) {
        $identifier = $this->options['expose']['identifier'];

        if (!empty($this->options['expose']['reduce'])) {
          $options = $this->reduce_value_options($options);

          if (!empty($this->options['expose']['multiple']) && empty($this->options['expose']['required'])) {
            $default_value = array();
          }
        }

        if (empty($this->options['expose']['multiple'])) {
          if (empty($this->options['expose']['required']) && (empty($default_value) || !empty($this->options['expose']['reduce']))) {
            $default_value = 'All';
          }
          elseif (empty($default_value)) {
            $keys = array_keys($options);
            $default_value = array_shift($keys);
          }
          // Due to #1464174 there is a chance that array('') was saved in the admin ui.
          // Let's choose a safe default value.
          elseif ($default_value == array('')) {
            $default_value = 'All';
          }
          else {
            $copy = $default_value;
            $default_value = array_shift($copy);
          }
        }
      }

      $form['value'] = array(
        '#type' => 'select',
        '#title' => t('Select units'),
        '#multiple' => TRUE,
        '#options' => $options,
        '#size' => min(9, count($options)),
        '#default_value' => $default_value,
      );
    }
  }

  function value_validate($form, &$form_state) {
    // We only validate if they've chosen the text field style.
    if ($this->options['type'] != 'textfield') {
      return;
    }

    $values = drupal_explode_tags($form_state['values']['options']['value']);
    $units = $this->validate_unit_strings($form['value'], $values);

    if ($units) {
      $form_state['values']['options']['value'] = $units;
    }
  }

  function accept_exposed_input($input) {
    if (empty($this->options['exposed'])) {
      return TRUE;
    }

    // If view is an attachment and is inheriting exposed filters, then assume
    // exposed input has already been validated
    if (!empty($this->view->is_attachment) && $this->view->display_handler->uses_exposed()) {
      $this->validated_exposed_input = (array) $this->view->exposed_raw_input[$this->options['expose']['identifier']];
    }

    // If it's non-required and there's no value don't bother filtering.
    if (!$this->options['expose']['required'] && empty($this->validated_exposed_input)) {
      return FALSE;
    }

    $rc = parent::accept_exposed_input($input);
    if ($rc) {
      // If we have previously validated input, override.
      if (!$this->is_a_group() && isset($this->validated_exposed_input)) {
        $this->value = $this->validated_exposed_input;
      }
    }

    return $rc;
  }

  function exposed_validate(&$form, &$form_state) {
    if (empty($this->options['exposed'])) {
      return;
    }

    $identifier = $this->options['expose']['identifier'];

    // We only validate if they've chosen the text field style.
    if ($this->options['type'] != 'textfield') {
      if ($form_state['values'][$identifier] != 'All')  {
        $this->validated_exposed_input = (array) $form_state['values'][$identifier];
      }
      return;
    }

    if (empty($this->options['expose']['identifier'])) {
      return;
    }

    $values = drupal_explode_tags($form_state['values'][$identifier]);

    $units = $this->validate_unit_strings($form[$identifier], $values);;
    if ($units) {
      $this->validated_exposed_input = $units;
    }
  }

  function validate_unit_strings(&$form, $values) {
    if (empty($values)) {
      return array();
    }

    $units = array();
    $names = array();
    $missing = array();
    foreach ($values as $value) {
      $missing[strtolower($value)] = TRUE;
      $names[] = $value;
    }

    if (!$names) {
      return FALSE;
    }

    $query = db_select('bat_units', 't');
    $query->fields('t');
    $query->condition('t.name', $names);
    $result = $query->execute();
    foreach ($result as $unit) {
      unset($missing[strtolower($unit->name)]);
      $units[] = $unit->unit_id;
    }

    if ($missing && !empty($this->options['error_message'])) {
      form_error($form, format_plural(count($missing), 'Unable to find unit: @units', 'Unable to find units: @units', array('@units' => implode(', ', array_keys($missing)))));
    }
    elseif ($missing && empty($this->options['error_message'])) {
      $units = array(0);
    }

    return $units;
  }

  function value_submit($form, &$form_state) {
    // prevent array_filter from messing up our arrays in parent submit.
  }

  function expose_form(&$form, &$form_state) {
    parent::expose_form($form, $form_state);
    if ($this->options['type'] != 'select') {
      unset($form['expose']['reduce']);
    }
    $form['error_message'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display error message'),
      '#default_value' => !empty($this->options['error_message']),
    );
  }

  function admin_summary() {
    $this->value_options = array();

    if ($this->value) {
      $this->value = array_filter($this->value);
      $result = bat_unit_load_multiple($this->value);
      foreach ($result as $entity) {
        $this->value_options[$entity->unit_id] = entity_label('bat_unit', $entity);
      }
    }
    return parent::admin_summary();
  }
}
