<?php

/**
 * @file
 * Unit Booking editing UI.
 *
 * We make very little use of the EntityAPI interface for this - preferring
 * instead to use views. That offers more flexibility to change a UI that will,
 * more often than not, be end-user facing.
 */

use \Drupal\bat_booking\AvailabilityAgent;

/**
 * UI controller.
 */
class BatBookingUIController extends EntityDefaultUIController {

  /**
   * Overrides hook_menu() defaults.
   *
   * Main reason for doing this is that
   * parent class hook_menu() is optimized for entity type administration.
   */
  public function hook_menu() {

    $items = array();
    $id_count = count(explode('/', $this->path));
    $wildcard = isset($this->entityInfo['admin ui']['menu wildcard']) ? $this->entityInfo['admin ui']['menu wildcard'] : '%' . $this->entityType;

    $items[$this->path] = array(
      'title' => 'Bookings',
      'description' => 'Add edit and update bookings.',
      'page callback' => 'system_admin_menu_block_page',
      'access arguments' => array('access administration pages'),
      'file path' => drupal_get_path('module', 'system'),
      'file' => 'system.admin.inc',
      'weight' => 10,
    );

    // Change the add page menu to multiple types of entities.
    $items[$this->path . '/add'] = array(
      'title' => 'Add a Booking',
      'description' => 'Add a new Booking',
      'page callback'  => 'bat_booking_add_page',
      'access callback'  => '_bat_booking_add_access',
      'type' => MENU_NORMAL_ITEM,
      'weight' => 20,
      'file' => 'bat_booking.admin.inc',
      'file path' => drupal_get_path('module', $this->entityInfo['module']),
    );

    // Add menu items to add each different type of unit.
    foreach (bat_booking_get_types() as $type) {
      $items[$this->path . '/add/' . $type->type] = array(
        'title' => 'Add @booking_type',
        'title arguments' => array('@booking_type' => $type->label),
        'page callback' => 'bat_booking_create_form_wrapper',
        'page arguments' => array($type->type),
        'access callback' => 'bat_booking_access',
        'access arguments' => array('create', bat_booking_create(array('type' => $type->type, 'uid' => 0))),
        'file' => 'bat_booking.admin.inc',
        'file path' => drupal_get_path('module', $this->entityInfo['module']),
      );
    }

    // Loading and editing Unit entities.
    $items[$this->path . '/booking/' . $wildcard] = array(
      'page callback' => 'bat_booking_form_wrapper',
      'page arguments' => array($id_count + 1),
      'access callback' => 'bat_booking_access',
      'access arguments' => array('update', $id_count + 1),
      'weight' => 0,
      'context' => MENU_CONTEXT_PAGE | MENU_CONTEXT_INLINE,
      'file' => 'bat_booking.admin.inc',
      'file path' => drupal_get_path('module', $this->entityInfo['module']),
    );
    $items[$this->path . '/booking/' . $wildcard . '/edit'] = array(
      'title' => 'Edit',
      'type' => MENU_DEFAULT_LOCAL_TASK,
      'weight' => -10,
      'context' => MENU_CONTEXT_PAGE | MENU_CONTEXT_INLINE,
    );

    $items[$this->path . '/booking/' . $wildcard . '/delete'] = array(
      'title' => 'Delete',
      'page callback' => 'bat_booking_delete_form_wrapper',
      'page arguments' => array($id_count + 1),
      'access callback' => 'bat_booking_access',
      'access arguments' => array('delete', $id_count + 1),
      'type' => MENU_LOCAL_TASK,
      'context' => MENU_CONTEXT_INLINE,
      'weight' => 10,
      'file' => 'bat_booking.admin.inc',
      'file path' => drupal_get_path('module', $this->entityInfo['module']),
    );

    // Menu item for viewing units.
    $items['booking/' . $wildcard] = array(
      'title callback' => 'bat_booking_page_title',
      'title arguments' => array(1),
      'page callback' => 'bat_booking_page_view',
      'page arguments' => array(1),
      'access callback' => 'bat_booking_access',
      'access arguments' => array('view', 1),
      'type' => MENU_CALLBACK,
    );
    return $items;
  }


  /**
   * Create the markup for the add Booking Entities page within the class
   * so it can easily be extended/overriden.
   */
  public function addPage() {
    $item = menu_get_item();
    $content = system_admin_menu_block($item);

    if (count($content) == 1) {
      $item = array_shift($content);
      drupal_goto($item['href']);
    }

    return array(
      '#theme' => 'bat_booking_add_list',
      '#content' => $content,
    );
  }

}

/**
 * Form callback wrapper: edit a Booking.
 *
 * @param $booking
 *   The  BatBooking object being edited by this form.
 *
 * @see bat_booking_edit_form()
 */
function bat_booking_form_wrapper($booking) {
  // Add the breadcrumb for the form's location.
  bat_booking_set_breadcrumb();

  // Set the page title.
  $type = bat_booking_get_types($booking->type);
  drupal_set_title(t('#@booking_id - @booking_type', array('@booking_type' => $type->label, '@booking_id' => $booking->booking_id)));
  $booking->date = format_date($booking->created, 'custom', 'Y-m-d H:i:s O');
  $account = user_load($booking->uid);
  $booking->owner_name = $account->name;

  return drupal_get_form('bat_booking_edit_form', $booking);
}

/**
 * Form callback wrapper: create a Booking.
 *
 * @param $booking
 *   The Booking object being edited by this form.
 *
 * @see bat_booking_edit_form()
 */
function bat_booking_create_form_wrapper($type) {
  global $user;
  // Add the breadcrumb for the form's location.
  bat_booking_set_breadcrumb();
  // Create a booking object.
  $booking = bat_booking_create(array('type' => $type));
  $booking->created = REQUEST_TIME;
  $booking->owner_name = $user->name;
  return drupal_get_form('bat_booking_edit_form', $booking);
}

/**
 * Form callback wrapper: delete a booking.
 *
 * @param $booking
 *   The booking object being edited by this form.
 *
 * @see bat_booking_edit_form()
 */
function bat_booking_delete_form_wrapper($booking) {
  // Add the breadcrumb for the form's location.
  bat_booking_set_breadcrumb();
  return drupal_get_form('bat_booking_delete_form', $booking);
}

/**
 * AJAX callback to update "edit profile" link after Customer field changes.
 *
 * @see bat_booking_edit_form()
 */
function bat_booking_edit_profile_callback(&$form, $form_state) {
  if (module_exists('commerce_customer')) {
    $client = explode(':', $form_state['values']['client']);

    if (isset($client[1])) {
      $result = db_select('field_data_commerce_customer_address')
        ->fields('field_data_commerce_customer_address', array('entity_id'))
        ->condition('commerce_customer_address_name_line', $client[0])
        ->condition('entity_id', $client[1])
        ->execute()
        ->fetchField();

      if ($result !== FALSE) {
        $form['client']['#field_suffix'] = t('<a href="@edit-profile" class="ctools-use-modal">edit profile</a>', array('@edit-profile' => url('admin/bat/customer-profiles/' . $client[1] . '/edit')));
      }
      else {
        $form['client']['#field_suffix'] = '';
      }
    }
    else {
      $form['client']['#field_suffix'] = '';
    }
  }

  return render($form['client']);
}

/**
 * Form callback: create or edit a booking.
 *
 * @param $booking
 *   The BatBooking object to edit or for a create form an empty booking object
 *     with only a booking type defined.
 */
function bat_booking_edit_form($form, &$form_state, $booking) {
  $form['#attributes']['class'][] = 'bat-management-form bat-booking-form';
  $form['#attached'] = array(
    'css' => array(drupal_get_path('module', 'bat_booking') . '/css/bat_booking.css'),
    'js' => array(drupal_get_path('module', 'bat') . '/js/bat_date_popup.js'),
  );

  $form['type'] = array(
    '#type' => 'value',
    '#value' => $booking->type,
  );

  $currency_setting = commerce_currency_load(commerce_default_currency());
  $currency_symbol = $currency_setting['symbol'];

  $form['data']['#tree'] = TRUE;

  // If price calculation is set to 'Price per person per night'.
  if (variable_get('bat_price_calculation', BAT_PER_NIGHT) == BAT_PER_PERSON) {
    // Show fields for enter children ages.
    $form['data']['group_size_children']['#ajax'] = array(
      'callback' => 'bat_booking_children_change_callback',
      'wrapper' => 'bat_booking_childrensage',
    );

    $form['data']['childrens_age'] = array(
      '#prefix' => '<div class="container-inline bat-booking-childrensage" id="bat_booking_childrensage">',
      '#suffix' => '</div>',
    );

    if (isset($form_state['values']['data']['group_size_children'])) {
      if ($persons >= $form_state['values']['data']['group_size_children']) {
        for ($t = 1; $t <= $form_state['values']['data']['group_size_children']; $t++) {
          $form['data']['childrens_age'][$t] = array(
            '#type' => 'textfield',
            '#title' => t('Age of child @num', array('@num' => $t)),
            '#size' => 5,
            '#maxlength' => 5,
            '#ajax' => array(
              'callback' => 'bat_booking_childrens_age_ajax_callback',
              'event' => 'blur',
            ),
          );
        }
      }
    }
    elseif (!empty($booking->data['childrens_age'])) {
      foreach ($booking->data['childrens_age'] as $key => $age) {
        $form['data']['childrens_age'][$key] = array(
          '#type' => 'textfield',
          '#title' => t('Age of child @num', array('@num' => $key)),
          '#size' => 5,
          '#maxlength' => 5,
          '#default_value' => $age,
          '#ajax' => array(
            'callback' => 'bat_booking_childrens_age_ajax_callback',
            'event' => 'blur',
          ),
        );
      }
    }
  }

  // A fieldset to hold the date range fields.
  $form['bat_date_range'] = array(
    '#type' => 'fieldset',
    '#attributes' => array('class' => array('bat-booking-date-range-wrapper')),
  );

  $form['bat_date_range'] += bat_date_range_fields();

  // Unset the default for max days away for bookings since we are on the admin.
  drupal_add_js(array('bat' => array('batBookingStartDay' => 0)), 'setting');

  // Set the default values for the dates.
  $form['bat_date_range']['bat_start_date']['#default_value'] = isset($booking->start_date) ? $booking->start_date : '';
  $form['bat_date_range']['bat_end_date']['#default_value'] = isset($booking->end_date) ? $booking->end_date : '';

  // Check startdate and enddate to avoid damage from dirty input.
  $startdate = '';
  if (isset($_GET['startdate'])) {
    $startdate = (is_numeric(check_plain($_GET['startdate']))) ? check_plain($_GET['startdate']) : '';
    if ($startdate != '') $form['bat_date_range']['bat_start_date']['#default_value'] = gmdate('Y-m-d', $startdate);
  }
  $enddate = '';
  if (isset($_GET['enddate'])) {
    $enddate = (is_numeric(check_plain($_GET['enddate']))) ? check_plain($_GET['enddate']) : '';
    if ($enddate != '') {
      $form['bat_date_range']['bat_end_date']['#default_value'] = gmdate('Y-m-d', $enddate);
    }
  }

  $unitid = '';
  if (isset($_GET['unitid'])) {
    $unitid = (is_numeric(check_plain($_GET['unitid']))) ? check_plain($_GET['unitid']) : '';

    if (bat_unit_load($unitid) == NULL || $startdate == '' || $enddate == '') {
      $unitid = '';
    }
  }

  // Access to availability information.
  $form['get_availability'] = array(
    '#type' => 'submit',
    '#value' => isset($booking->unit_id) ? t('Re-assign Unit') : t('Check availability'),
    '#submit' => array('bat_booking_edit_form_availability_submit'),
    '#validate' => array('bat_form_start_end_dates_validate'),
    '#ajax' => array(
      'callback' => 'bat_booking_edit_form_availability_callback',
      'wrapper' => 'availability-wrapper',
    ),
    '#prefix' => '<div class="bat-booking-availability-button-wrapper" id="availability-button">',
    '#suffix' => '</div>',
  );

  $options_list = '';

  // If a unit is assigned get the unit type label.
  if (isset($booking->unit_id)) {
    $unit = bat_unit_load($booking->unit_id);
    $type_obj = bat_unit_type_load($unit->type);

    $form['unit_id'] = array(
      '#type' => 'hidden',
      '#value' => $booking->unit_id,
    );
  }

  $unit_selected = isset($booking->unit_id) ? t('Currently assigned unit: @unit_name / @type', array('@type' => $type_obj->label, '@unit_name' => $unit->name)) : '';

  if ($options_list != '') {
    $unit_selected .= ' ' . t('(Options: !options)', array('!options' => substr($options_list, 0, strlen($options_list) - 2)));
  }
  if (isset($booking->price)) {
    $unit_selected .= '<br />' . t('Price') . ': ' .
    t('@currency_symbol @amount', array( // TODO: replace with a proper currency formatter
      '@currency_symbol' => $currency_symbol,
      '@amount' => number_format($booking->price / 100, 2, '.', ''),
    ));
  }

  $form['availability_fieldset'] = array(
    '#type' => 'fieldset',
    '#description' => $unit_selected,
    '#prefix' => '<div class="bat-booking-availability-wrapper" id="availability-wrapper">',
    '#suffix' => '</div>',
  );

  // If submit above pressed and we have dates we can process to assign a unit.
  if (isset($form_state['show_availability_options']) || $unitid != '') {
    $types = bat_booking_edit_form_get_unit_types();

    // Figure out which is the current unit and mark that as selected.
    $selected = '';
    if (isset($form_state['values']['unit_type'])) {
      $selected = $form_state['values']['unit_type'];
    }
    elseif (isset($booking->unit_type)) {
      $selected = $booking->unit_type;
    }
    elseif ($unitid != '') {
      $unit = bat_unit_load($unitid);
      $selected = $unit->type;

      $form_state['values']['unit_type'] = $selected;
      $form_state['input']['bat_start_date']['date'] = gmdate('d/m/Y', $startdate);
      $form_state['input']['bat_end_date']['date'] = gmdate('d/m/Y', $enddate);
      $form_state['values']['bat_start_date'] = gmdate('Y-m-d', $startdate);
      $form_state['values']['bat_end_date'] = gmdate('Y-m-d', $enddate);

      $form_state['show_availability_options'] = TRUE;
    }

    // Set the unit types - everytime a different type is chosen the available
    // units for that type pop-up.
    $form['availability_fieldset']['unit_type'] = array(
      '#type' => 'select',
      '#title' => t('Unit Type'),
      '#options' => $types,
      '#empty_option' => t('- Select -'),
      '#default_value' => $selected,
      '#ajax' => array(
        'callback' => 'bat_booking_edit_form_unit_type_callback',
        'wrapper' => 'unit-wrapper',
      ),
    );
    unset($form['unit_id']);

    // This is where the available units will pop up.
    $form['availability_fieldset']['unit_fieldset'] = array(
      '#type' => 'fieldset',
      '#prefix' => '<div class="bat-booking-unit-wrapper" id="unit-wrapper">',
      '#suffix' => '</div>',
    );

    $form['availability_fieldset']['unit_fieldset']['instructions'] = array();

    // If the user has selected a unit type or we already have a unit assigned
    // show all the available units of that type (Also the already assign unit).
    if (isset($form_state['values']['unit_type']) || isset($booking->unit_type)) {

      $search_unit_type = '';
      if (isset($form_state['values']['unit_type'])) {
        $search_unit_type = $form_state['values']['unit_type'];
      }
      elseif (isset($booking->unit_type)) {
        $search_unit_type = $booking->unit_type;
      }

      if (!empty($search_unit_type)) {
        $childrens_age = array();
        if (isset($form_state['values']['data']['childrens_age'])) {
          $childrens_age = $form_state['values']['data']['childrens_age'];
        }

        $group_size_children = (isset($form_state['input']['data']['group_size_children'])) ? $form_state['input']['data']['group_size_children'] : 0;
        list($start_date, $end_date) = bat_form_input_get_start_end_dates($form_state);
        $available_units = bat_booking_edit_form_get_units($search_unit_type, $booking, $start_date, $end_date);
        if ($available_units == BAT_NO_MATCH) {
          $form['availability_fieldset']['unit_fieldset']['instructions'] = array(
            '#markup' => t('No @unit_type units are available for specified party size on the chosen dates.', array('@unit_type' => $search_unit_type)),
          );
        }
        else {
          foreach ($available_units as $type => $units_per_price) {
            // Load the type obj and set a title.
            $type_obj = bat_unit_type_load($type);
            $form['availability_fieldset']['unit_fieldset']['type'] = array(
              '#type' => 'fieldset',
              '#title' => t('@unit_type available bookable units', array('@unit_type' => $type_obj->label)),
              '#attributes' => array('class' => array('bat-booking-unit-type-wrapper')),
            );

            $form['availability_fieldset']['unit_fieldset']['instructions'] = array(
              '#markup' => t('The following units are available for the chosen dates; please select one.'),
            );

            $options = array();
            // Create elements based on type/price categorisation.
            foreach ($units_per_price as $price => $units) {
              foreach ($units as $unit) {
                $options[$unit['unit']->unit_id] = $unit['unit']->name . ' - ' . t('Cost') . ': ' .
                  t('@currency_symbol @amount', array( // TODO: replace with a proper currency formatter
                      '@currency_symbol' => $currency_symbol,
                      '@amount' => $units[key($units)]['price'])
                  );
              }
            }

            $form['availability_fieldset']['unit_fieldset']['type']['unit_id'] = array(
              '#type' => 'radios',
              '#options' => $options,
              '#validated' => TRUE,
              '#default_value' => isset($booking->unit_id) ? $booking->unit_id : '',
              '#ajax' => array(
                'callback' => 'bat_booking_select_unit_callback',
                'wrapper' => 'options-wrapper',
              ),
            );

            if (isset($unitid)) {
              $form['availability_fieldset']['unit_fieldset']['type']['unit_id']['#default_value'] = $unitid;
            }

            $form['availability_fieldset']['unit_fieldset']['options_fieldset'] = array(
              '#type' => 'fieldset',
              '#prefix' => '<div class="bat-booking-options-wrapper" id="options-wrapper">',
              '#suffix' => '</div>',
            );
          }
        }
      }
      else {
        $form['availability_fieldset']['unit_fieldset']['instructions'] = array(
          '#markup' => t('You must select a Unit type to choose a unit'),
        );
      }
    }
  }

  // Add the field related form elements.
  $form_state['bat_booking'] = $booking;
  field_attach_form('bat_booking', $booking, $form, $form_state);

  // Set an #ajax callback for start/end date elements for edited bookings.
  if (is_numeric($booking->booking_id)) {
    $form['bat_date_range']['bat_start_date']['#ajax'] = array(
      'callback' => 'bat_booking_date_ajax_callback',
      'event' => 'blur',
    );
    $form['bat_date_range']['bat_end_date']['#ajax'] = array(
      'callback' => 'bat_booking_date_ajax_callback',
      'event' => 'blur',
    );
  }

  // Management vertical tabs.
  $form['additional_settings'] = array(
    '#type' => 'vertical_tabs',
    '#weight' => 99,
  );

  $form['b_status'] = array(
    '#type' => 'fieldset',
    '#title' => t('Booking status'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#group' => 'additional_settings',
  );
  $form['b_status']['booking_status'] = array(
    '#type' => 'checkbox',
    '#title' => t('Booking Confirmed'),
    '#options' => array(
      '0' => '0',
      '1' => '1',
    ),
    '#default_value' => isset($booking->booking_status) ? $booking->booking_status : '0',
  );

  // Add the user account and e-mail fields.
  $form['user'] = array(
    '#type' => 'fieldset',
    '#title' => t('User information'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#access' => user_access('bypass bat_booking entities access'),
    '#group' => 'additional_settings',
    '#attached' => array(
      'js' => array(
        drupal_get_path('module', 'bat_booking') . '/js/bat_booking.js',
        array(
          'type' => 'setting',
          'data' => array('anonymous' => variable_get('anonymous', t('Anonymous'))),
        ),
      ),
    ),
    '#weight' => 30,
  );
  $form['user']['owner_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Owned by'),
    '#description' => t('Leave blank for %anonymous.', array('%anonymous' => variable_get('anonymous', t('Anonymous')))),
    '#maxlength' => 60,
    '#autocomplete_path' => 'user/autocomplete',
    '#default_value' => !empty($booking->owner_name) ? $booking->owner_name : '',
    '#weight' => -1,
  );

  // Add a log checkbox and timestamp field to a history tab.
  $form['booking_history'] = array(
    '#type' => 'fieldset',
    '#title' => t('Booking history'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#group' => 'additional_settings',
    '#attached' => array(
      'js' => array(drupal_get_path('module', 'bat_booking') . '/js/bat_booking.js'),
    ),
    '#weight' => 40,
  );
  $form['booking_history']['date'] = array(
    '#type' => 'textfield',
    '#title' => t('Created on'),
    '#description' => t('Format: %time. The date format is YYYY-MM-DD and %timezone is the time zone offset from UTC. Leave blank to use the time of form submission.', array(
      '%time' => !empty($booking->date) ? date_format(date_create($booking->date), 'Y-m-d H:i:s O') : format_date($booking->created, 'custom', 'Y-m-d H:i:s O'),
      '%timezone' => !empty($booking->date) ? date_format(date_create($booking->date), 'O') : format_date($booking->created, 'custom', 'O'),
    )),
    '#maxlength' => 25,
    '#default_value' => !empty($booking->created) ? format_date($booking->created, 'custom', 'Y-m-d H:i:s O') : '',
  );
  $form['booking_history']['created'] = array(
    '#type' => 'hidden',
    '#value' => !empty($booking->created) ? format_date($booking->created, 'short') : '',
    '#attributes' => array('id' => 'edit-created'),
  );
  $form['booking_history']['changed'] = array(
    '#type' => 'hidden',
    '#value' => !empty($booking->changed) ? format_date($booking->changed, 'short') : '',
    '#attributes' => array('id' => 'edit-changed'),
  );

  $form['actions'] = array(
    '#type' => 'container',
    '#attributes' => array('class' => array('form-actions')),
    '#weight' => 400,
  );

  // We add the form's #submit array to this button along with the actual submit
  // handler to preserve any submit handlers added by a form callback_wrapper.
  $submit = array();

  if (!empty($form['#submit'])) {
    $submit += $form['#submit'];
  }

  // Wrapper to show validation messages where the assigned dates are changed
  // and the price is not re-assigned.
  $form['availability_fieldset']['assigned_dates_msg'] = array(
    '#markup' => '<div id="assigned-dates-msg"></div>',
  );

  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Booking'),
    '#submit' => $submit + array('bat_booking_edit_form_submit'),
  );

  if (!isset($booking->is_new) && bat_booking_access('delete', $booking)) {
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete Booking'),
      '#submit' => $submit + array('bat_booking_form_submit_delete'),
      '#weight' => 45,

    );
  }

  // Depending on whether the form is in a popup or a normal page we need to change
  // the behavior of the cancel button
  if (isset($form_state['ajax']) && $form_state['ajax'] == TRUE) {
    $form['actions']['cancel'] = array(
      '#markup' => l(t('Cancel'), 'admin/bat/bookings', array('attributes' => array('class' => array('ctools-close-modal')))),
      '#weight' => 50,
    );
  }
  else {
    $form['actions']['cancel'] = array(
      '#markup' => l(t('Cancel'), 'admin/bat/bookings'),
      '#weight' => 50,
    );
  }

  // We append the validate handler to #validate in case a form callback_wrapper
  // is used to add validate handlers earlier.
  $form['#validate'][] = 'bat_form_start_end_dates_validate';
  $form['#validate'][] = 'bat_booking_edit_form_validate';
  return $form;
}

/**
 * After build callback for the bat_booking_edit_form.
 */
function bat_booking_edit_form_add_modal_js($form, &$form_state) {
  ctools_include('modal');
  ctools_modal_add_js();

  return $form;
}

/**
 * AJAX callback for the end date admin form element.
 *
 * Triggers automatically the re-assign submit button and help the user to don't
 * forget re-assign price.
 *
 * @see bat_booking_edit_form()
 */
function bat_booking_date_ajax_callback($form, &$form_state) {
  $commands[] = ajax_command_invoke('#edit-get-availability', 'trigger', array('mousedown'));
  print ajax_render($commands);
  exit();
}

/**
 * AJAX callback for the children ages form element.
 *
 * Triggers automatically the re-assign submit button and help the user to don't
 * forget re-assign price.
 *
 * @see bat_booking_edit_form()
 */
function bat_booking_childrens_age_ajax_callback($form, &$form_state) {
  if (isset($form_state['values']['unit_id'])) {
    $commands[] = ajax_command_invoke('#edit-get-availability', 'trigger', array('mousedown'));
    print ajax_render($commands);
  }

  exit();
}

/**
 * AJAX callback for the children form selector to show textfields for ages.
 *
 * @see bat_booking_edit_form()
 */
function bat_booking_children_change_callback($form, &$form_state) {
  return $form['data']['childrens_age'];
}

/**
 * Submit callback for Assign unit button.
 *
 * When the user presses the assign unit button we rebuild the form and
 * show the available units
 */
function bat_booking_edit_form_availability_submit($form, &$form_state) {
  $form_state['show_availability_options'] = TRUE;
  $form_state['rebuild'] = TRUE;
}


/**
 * AJAX callback for Assign unit button.
 *
 * @see bat_booking_edit_form()
 */
function bat_booking_edit_form_availability_callback(&$form, $form_state) {
  return $form['availability_fieldset'];
}

/**
 * AJAX callback for unit and unit options form elements.
 *
 * @see bat_booking_edit_form()
 */
function bat_booking_select_unit_callback(&$form, $form_state) {
  return $form['availability_fieldset']['unit_fieldset']['options_fieldset'];
}

/**
 * AJAX callback for group size form elements.
 *
 * @see bat_booking_edit_form()
 */
function bat_booking_group_size_ajax_callback(&$form, $form_state) {
  $commands = array();
  $commands[] = ajax_command_replace('#bat_booking_group_size_children_container', render($form['data']['group_size_children']));
  $commands[] = ajax_command_replace('#bat_booking_childrensage', render($form['data']['childrens_age']));

  if (isset($form_state['values']['unit_id'])) {
    $commands[] = ajax_command_invoke('#edit-get-availability', 'trigger', array('mousedown'));
  }

  return array('#type' => 'ajax', '#commands' => $commands);
}

/**
 * Callback for the actual list of available units.
 */
function bat_booking_edit_form_unit_type_callback(&$form, $form_state) {
  return $form['availability_fieldset']['unit_fieldset'];
}

/**
 * Returns a list of unit types user can edit.
 */
function bat_booking_edit_form_get_unit_types() {
  $efq = new EntityFieldQuery();
  $efq->entityCondition('entity_type', 'bat_unit');
  $efq->addTag('bat_availability_access');
  $result = $efq->execute();
  $entities = entity_load('bat_unit', array_keys($result['bat_unit']));
  $types = array();
  $all_types = bat_unit_get_types();
  foreach ($entities as $entity) {
    if (!isset($types[$entity->type])) {
      $current_type = $all_types[$entity->type];
      $types[$entity->type] = $current_type->label;
    }
  }
  return $types;
}

/**
 * Returns a set of available units given a unit type, start and end dates.
 *
 * It makes special provisions for the currently selected unit - to consider
 * that as "available" as well.
 *
 * @param string $unit_type
 *   The unit type.
 * @param BatBooking $current_booking
 *   The current booking.
 * @param DateTime $start_date
 *   Start date.
 * @param DateTime $end_date
 *   End date.
 * @param int $group_size
 *   Group size.
 * @param int $group_size_children
 *   Number of children in the group.
 * @param array $children_age
 *   Children age array.
 *
 * @return array|int
 *   Array containing available units or integer to indicate error code.
 */
function bat_booking_edit_form_get_units($unit_type, $current_booking, $start_date, $end_date) {

  // Set the unit types,
  $unit_types = array($unit_type);
  $current_booking_state = '';

  $bookable_units = array();

  // The current unit for this booking should also return as available.
  if (isset($current_booking->booking_status) && isset($current_booking->booking_id)) {
    $current_booking_state = bat_availability_assign_id($current_booking->booking_id, $current_booking->booking_status);
  }

  $valid_states = array_keys(array_filter(variable_get('bat_valid_availability_states', drupal_map_assoc(array(BAT_AVAILABLE, BAT_ON_REQUEST)))));

  if (variable_get('bat_price_calculation', BAT_PER_NIGHT) == BAT_PER_PERSON) {
    $booking_parameters = array(array('adults' => 1, 'children' => 0, 'childrens_age' => array()));
  }
  else {
    $booking_parameters = array(array('adults' => 1, 'children' => 0));
  }

  if (!empty($current_booking->booking_id)) {
    $ac = new AvailabilityAgent($start_date, $end_date, $booking_parameters, 1, array_merge($valid_states, array(bat_availability_assign_id($current_booking->booking_id, '0'), bat_availability_assign_id($current_booking->booking_id))), $unit_types);
  }
  else {
    $ac = new AvailabilityAgent($start_date, $end_date, $booking_parameters, 1, array_merge($valid_states, array($current_booking_state)), $unit_types);
  }
  $available_units = $ac->checkAvailability(TRUE);

  if (is_array($available_units)) {
    foreach ($available_units as $type => $units_per_price) {
      foreach ($units_per_price as $price => $units) {
        foreach ($units as $unit_id => $unit) {
          if (bat_unit_access('update availability', $unit['unit'])) {
            $bookable_units[$type][$price][$unit_id] = $unit;
          }
        }
      }
    }
  }

  return empty($bookable_units) ? BAT_NO_MATCH : $bookable_units;
}


/**
 * Form API validate callback for the booking form.
 */
function bat_booking_edit_form_validate(&$form, &$form_state) {
  list($start_date, $end_date) = bat_form_input_get_start_end_dates($form_state);

  if ($start_date && $end_date) {
    // Convert input format to the same used in the stored dates.
    $input_start_date = $start_date->format('Y-m-d');
    $input_end_date = $end_date->format('Y-m-d');
    // If stored dates differ from input dates check that price was re-assigned.
    if (isset($booking->start_date) && isset($booking->end_date)) {
      if ($input_start_date != $booking->start_date || $input_end_date != $booking->end_date) {
        if (!isset($form_state['values']['unit_id']) || !is_numeric($form_state['values']['unit_id'])) {
          form_set_error('availability_fieldset', t('A unit must be re-assigned for this booking.'));
        }
      }
    }
  }

  // Check if unit assigned.
  if ((!isset($form_state['values']['unit_id'])) || ($form_state['values']['unit_id'] == '')) {
    form_set_error('availability_fieldset', t('A unit has not been assigned for this booking.'));
  }
  elseif ((isset($form_state['values']['unit_id'])) && ($form_state['values']['unit_id'] == '')) {
    form_set_error('availability_fieldset', t('A unit has not been assigned for this booking.'));
  }

  $booking = $form_state['bat_booking'];
  if (isset($booking->unit_id)) {
    $new_unit_id = $booking->unit_id;
    if (isset($form_state['values']['unit_id'])) {
      $new_unit_id = $form_state['values']['unit_id'];
    }

    if ($new_unit_id == $booking->unit_id) {
      // We are going to be updating the event. So the removing the old event.
      if (($booking->unit_id != 0) && $start_date && $end_date) {
        // Create a calendar.
        $uc = new UnitCalendar($booking->unit_id);

        $adjusted_end_date = clone($end_date);
        $adjusted_end_date->modify('-1 day');

        // Create an event representing the event to remove.
        $event_id = bat_availability_assign_id($booking->booking_id, $booking->booking_status);
        $be = new BookingEvent($booking->unit_id, $event_id, $start_date, $end_date);

        // Check if a locked event is blocking the update.
        $states_confirmed = $uc->getStates($start_date, $adjusted_end_date);

        $valid_states = array_keys(array_filter(variable_get('bat_valid_availability_states', drupal_map_assoc(array(BAT_AVAILABLE, BAT_ON_REQUEST)))));
        $valid_states = array_merge($valid_states, array(BAT_UNCONFIRMED_BOOKINGS, $be->id));

        $state_diff_confirmed = array_diff($states_confirmed, $valid_states);

        if (count($state_diff_confirmed) > 0) {
          $date1 = new DateTime($form['bat_date_range']['bat_start_date']['#default_value']);
          $date2 = new DateTime($form['bat_date_range']['bat_end_date']['#default_value']);

          $form['bat_date_range']['bat_start_date']['date']['#value'] = $date1->format($form['bat_date_range']['bat_start_date']['#date_format']);
          $form['bat_date_range']['bat_end_date']['date']['#value'] = $date2->format($form['bat_date_range']['bat_end_date']['#date_format']);

          form_set_error('date_range', t('Could not update calendar because a locked event is blocking the update - you need to unlock any locked events in that period.'));
        }

        // Check if this booking overlap with an unconfirmed booking.
        $states_unconfirmed = $uc->getStates($start_date, $adjusted_end_date, TRUE);

        $valid_states = array_keys(array_filter(variable_get('bat_valid_availability_states', drupal_map_assoc(array(BAT_AVAILABLE, BAT_ON_REQUEST)))));
        $valid_states = array_merge($valid_states, array($be->id));

        $state_diff_unconfirmed = array_diff($states_unconfirmed, $valid_states);

        if (count($state_diff_unconfirmed) > 0) {
          $booking_id = bat_availability_return_id(array_pop($state_diff_unconfirmed));
          form_set_error('date_range', t('Change of dates leads to an overlap with an unconfirmed booking, please resolve issue with booking !booking_link first.', array('!booking_link' => l($booking_id, 'admin/bat/bookings/booking/' . $booking_id . '/edit'))));
        }
      }
    }
  }

  // Notify field widgets to validate their data.
  entity_form_field_validate('bat_booking', $form, $form_state);
}

/**
 * Form API submit callback for the Booking form.
 */
function bat_booking_edit_form_submit(&$form, &$form_state) {
  // We also need appropriate named variables for start and end date.
  // It's simpler to do this than change all the other code for now.
  $form_state['values']['start_date'] = $form_state['values']['bat_start_date'];
  $form_state['values']['end_date'] = $form_state['values']['bat_end_date'];

  $unit_id = 0;
  if (isset($form_state['values']['unit_id'])) {
    $unit_id = $form_state['values']['unit_id'];
  }

  // If we are dealing with a new booking.
  if ($form_state['bat_booking']->booking_id == '') {

    $booking = bat_booking_create(array('type' => $form_state['bat_booking']->type));
    $form_state['bat_booking'] = $booking;

    $booking = entity_ui_controller('bat_booking')->entityFormSubmitBuildEntity($form, $form_state);

    $booking->is_new = isset($booking->is_new) ? $booking->is_new : 0;
  }
  else {
    $booking = entity_ui_controller('bat_booking')->entityFormSubmitBuildEntity($form, $form_state);

    // Get the unit id from the booking to be used further down as it is not in
    // the form_values because of how unit ids are assigned.
    $unit_id = $booking->unit_id;
  }

  // Add in created and changed times.
  $booking->created = !empty($booking->date) ? strtotime($booking->date) : REQUEST_TIME;
  $booking->changed = time();

  // Add in the booking owner.
  if ($account = user_load_by_name($booking->owner_name)) {
    $booking->uid = $account->uid;
  }
  else {
    $booking->uid = 0;
  }

  if (isset($form_state['input']['price'])) {
    $booking->price = $form_state['input']['price'] * 100;
  }

  $unit = bat_unit_load($unit_id);

  // Save booking.
  $booking->save();

  // Add the booking to $form_state to be altered by other submit handlers.
  $form_state['booking'] = $booking;

  // We have a unit defined so lets block availability there.
  if ($unit_id != 0) {
    if ($booking->bat_av_update == BAT_UPDATED) {
      drupal_set_message(t('Unit availability has been updated.'));
    }
    else {
      drupal_set_message(t('Unit availability could not be updated.'), 'error');
    }
  }

  $form_state['redirect'] = 'admin/bat/bookings';
}

/**
 * Form API submit callback for the delete button.
 */
function bat_booking_form_submit_delete($form, &$form_state) {
  if (isset($form_state['ajax'])) {
    bat_booking_delete($form_state['bat_booking']);
    drupal_set_message(t('The booking has been removed'));
    $form_state['booking_deleted'] = TRUE;
  }
  else {
    $form_state['redirect'] = 'admin/bat/bookings/booking/' . $form_state['bat_booking']->booking_id . '/delete';
  }
}

/**
 * Form callback: confirmation form for deleting a booking.
 *
 * @param $booking
 *   The booking to delete.
 *
 * @see confirm_form()
 */
function bat_booking_delete_form($form, &$form_state, $booking) {
  $form_state['bat_booking'] = $booking;

  $form['#submit'][] = 'bat_booking_delete_form_submit';

  $form = confirm_form($form,
    t('Are you sure you want to delete Booking ?'),
    'admin/bat/bookings/booking',
    '<p>' . t('This action cannot be undone.') . '</p>',
    t('Delete'),
    t('Cancel'),
    'confirm'
  );

  return $form;
}

/**
 * Submit callback for booking_delete_form.
 */
function bat_booking_delete_form_submit($form, &$form_state) {
  $booking = $form_state['bat_booking'];

  bat_booking_delete($booking);

  drupal_set_message(t('The booking has been deleted.'));
  watchdog('bat', 'Deleted booking %booking_id.', array('%booking_id' => $booking->booking_id));

  $form_state['redirect'] = 'admin/bat/bookings';
}

/**
 * Page to add Units.
 *
 * @todo Pass this through a proper theme function
 */
function bat_booking_add_page() {
  $controller = entity_ui_controller('bat_booking');
  return $controller->addPage();
}

/**
 * Displays the list of available unit types for unit creation.
 *
 * @ingroup themeable
 */
function theme_bat_booking_add_list($variables) {
  $content = $variables['content'];
  if ($content) {
    $output = '<dl class="booking-type-list">';
    foreach ($content as $item) {
      $output .= '<dt>' . l($item['title'], $item['href']) . '</dt>';
      $output .= '<dd>' . filter_xss_admin($item['description']) . '</dd>';
    }
    $output .= '</dl>';
  }
  else {
    if (user_access('administer booking types')) {
      $output = '<p>' . t('Bookings cannot be added because you have not created any booking types yet. Go to the <a href="@create-booking-type">booking type creation page</a> to add a new booking type.', array('@create-booking-type' => url('admin/bat/bookings/booking-types/add'))) . '</p>';
    }
    else {
      $output = '<p>' . t('No booking types have been created yet for you to use.') . '</p>';
    }
  }
  return $output;
}

/**
 * Sets the breadcrumb for administrative unit pages.
 */
function bat_booking_set_breadcrumb() {
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    l(t('Administration'), 'admin'),
    l(t('BAT'), 'admin/bat'),
    l(t('Bookings'), 'admin/bat/bookings'),
  );

  drupal_set_breadcrumb($breadcrumb);
}
