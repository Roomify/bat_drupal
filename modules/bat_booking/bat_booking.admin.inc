<?php

/**
 * @file
 * BatBooking editing UI.
 */

/**
 * UI controller.
 */
class BatBookingUIController extends EntityDefaultUIController {

  /**
   * Overrides hook_menu() defaults.
   */
  public function hook_menu() {
    $items = parent::hook_menu();

    // Change the add page menu to multiple types of entities.
    $items[$this->path . '/add']['title'] = 'Add a Booking';
    $items[$this->path . '/add']['description'] = 'Create a new booking.';
    $items[$this->path . '/add']['page callback'] = 'bat_booking_add_page';
    $items[$this->path . '/add']['access callback'] = 'bat_booking_add_access';
    unset($items[$this->path . '/add']['title callback']);

    // Add menu items to add each different type of bookings.
    foreach (bat_booking_get_types() as $booking_type) {
      $items[$this->path . '/add/' . $booking_type->type] = array(
        'title' => 'Add @booking_type_label booking',
        'title arguments' => array('@booking_type_label' => $booking_type->label),
        'page callback' => 'bat_booking_create_form_wrapper',
        'page arguments' => array($booking_type->type),
        'access callback' => 'bat_booking_access',
        'access arguments' => array('create', bat_booking_create(array('type' => $booking_type->type, 'uid' => 0))),
        'file' => 'bat_booking.admin.inc',
        'file path' => drupal_get_path('module', $this->entityInfo['module']),
      );
    }

    return $items;
  }

  /**
   * Overrides overviewTableHeaders() defaults.
   */
  protected function overviewTableHeaders($conditions, $rows, $additional_header = array()) {
    $header = array(
      t('ID'),
      t('Name'),
      t('Bundle'),
    );
    if (!empty($this->entityInfo['exportable'])) {
      $header[] = t('Status');
    }
    // Add operations with the right colspan.
    $header[] = array('data' => t('Operations'), 'colspan' => $this->operationCount());
    return $header;
  }

  /**
   * Overrides overviewTableRow() defaults.
   */
  protected function overviewTableRow($conditions, $id, $entity, $additional_cols = array()) {
    $entity_uri = entity_uri($this->entityType, $entity);

    $row[] = $id;

    $row[] = array(
      'data' => array(
        '#theme' => 'entity_ui_overview_item',
        '#label' => entity_label($this->entityType, $entity),
        '#name' => !empty($this->entityInfo['exportable']) ? entity_id($this->entityType, $entity) : FALSE,
        '#url' => $entity_uri ? $entity_uri : FALSE,
        '#entity_type' => $this->entityType,
      ),
    );

    $booking_type = bat_booking_type_load($entity->type);
    $row[] = $booking_type->label;

    // Add a row for the exportable status.
    if (!empty($this->entityInfo['exportable'])) {
      $row[] = array(
        'data' => array(
          '#theme' => 'entity_status',
          '#status' => $entity->{$this->statusKey},
        ),
      );
    }
    // In case this is a bundle, we add links to the field ui tabs.
    $field_ui = !empty($this->entityInfo['bundle of']) && entity_type_is_fieldable($this->entityInfo['bundle of']) && module_exists('field_ui');
    // For exportable entities we add an export link.
    $exportable = !empty($this->entityInfo['exportable']);
    // If i18n integration is enabled, add a link to the translate tab.
    $i18n = !empty($this->entityInfo['i18n controller class']);

    // Add operations depending on the status.
    if (entity_has_status($this->entityType, $entity, ENTITY_FIXED)) {
      $row[] = array('data' => l(t('clone'), $this->path . '/manage/' . $id . '/clone'), 'colspan' => $this->operationCount());
    }
    else {
      $row[] = l(t('edit'), $this->path . '/manage/' . $id);

      if ($field_ui) {
        $row[] = l(t('manage fields'), $this->path . '/manage/' . $id . '/fields');
        $row[] = l(t('manage display'), $this->path . '/manage/' . $id . '/display');
      }
      if ($i18n) {
        $row[] = l(t('translate'), $this->path . '/manage/' . $id . '/translate');
      }
      if ($exportable) {
        $row[] = l(t('clone'), $this->path . '/manage/' . $id . '/clone');
      }

      if (empty($this->entityInfo['exportable']) || !entity_has_status($this->entityType, $entity, ENTITY_IN_CODE)) {
        $row[] = l(t('delete'), $this->path . '/manage/' . $id . '/delete', array('query' => drupal_get_destination()));
      }
      elseif (entity_has_status($this->entityType, $entity, ENTITY_OVERRIDDEN)) {
        $row[] = l(t('revert'), $this->path . '/manage/' . $id . '/revert', array('query' => drupal_get_destination()));
      }
      else {
        $row[] = '';
      }
    }
    if ($exportable) {
      $row[] = l(t('export'), $this->path . '/manage/' . $id . '/export');
    }
    return $row;
  }

  /**
   * Creates the markup for the add Booking Entities page within the class
   * so it can easily be extended/overridden.
   */
  public function addPage() {
    $item = menu_get_item();
    $booking_types = bat_booking_get_types();

    if (count($booking_types) == 1) {
      $booking_type = reset($booking_types);
      drupal_goto($this->path . '/add/' . $booking_type->type);
    }

    $items = array();
    foreach ($booking_types as $booking_type) {
      $items[] = array(
        'title' => t('Add @booking_type_label booking', array('@booking_type_label' => $booking_type->label)),
        'href' => $this->path . '/add/' . $booking_type->type,
        'description' => '',
      );
    }

    return array(
      '#theme' => 'bat_booking_add_list',
      '#content' => $items,
    );
  }

}

/**
 *
 */
function bat_booking_form($form, &$form_state, $booking, $op = 'edit') {
  global $user;
  // Add the breadcrumb for the form's location.
  bat_booking_set_breadcrumb();
  drupal_set_title(t('Edit @booking_label', array('@booking_label' => $booking->label)));

  $booking->date = format_date($booking->created, 'custom', 'Y-m-d H:i:s O');
  $account = user_load($booking->uid);
  $booking->author_name = isset($account->name) ? $account->name : '';

  return bat_booking_edit_form($form, $form_state, $booking);
}

/**
 * Form callback wrapper: create a Booking.
 *
 * @param string $type
 *   The Booking type for the booking to be created.
 */
function bat_booking_create_form_wrapper($type) {
  global $user;
  // Add the breadcrumb for the form's location.
  bat_booking_set_breadcrumb();

  $booking = bat_booking_create(array('type' => $type, 'uid' => $user->uid));
  $booking->created = REQUEST_TIME;
  $booking->author_name = $user->name;
  $booking->status = 1;

  return drupal_get_form('bat_booking_edit_form', $booking);
}

/**
 * Generates the booking editing form.
 */
function bat_booking_edit_form($form, &$form_state, $booking) {
  // Add the field related form elements.
  $form_state['bat_booking'] = $booking;
  field_attach_form('bat_booking', $booking, $form, $form_state, isset($booking->language) ? $booking->language : NULL);
  $form['additional_settings'] = array(
    '#type' => 'vertical_tabs',
    '#weight' => 99,
  );

  // Type author information for administrators.
  $form['author'] = array(
    '#type' => 'fieldset',
    '#access' => user_access('bypass bat_booking entities access'),
    '#title' => t('Authoring information'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#group' => 'additional_settings',
    '#attributes' => array(
      'class' => array('type-form-author'),
    ),
    '#attached' => array(
      'js' => array(
        array(
          'type' => 'setting',
          'data' => array('anonymous' => variable_get('anonymous', t('Anonymous'))),
        ),
      ),
    ),
    '#weight' => 90,
  );

  $form['type'] = array(
    '#type' => 'value',
    '#value' => $booking->type,
  );

  $form['author']['author_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Authored by'),
    '#maxlength' => 60,
    '#autocomplete_path' => 'user/autocomplete',
    '#default_value' => !empty($booking->author_name) ? $booking->author_name : '',
    '#weight' => -1,
    '#description' => t('Leave blank for %anonymous.', array('%anonymous' => variable_get('anonymous', t('Anonymous')))),
  );
  $form['author']['date'] = array(
    '#type' => 'textfield',
    '#title' => t('Authored on'),
    '#maxlength' => 25,
    '#description' => t('Format: %time. The date format is YYYY-MM-DD and %timezone is the time zone offset from UTC. Leave blank to use the time of form submission.', array('%time' => !empty($booking->date) ? date_format(date_create($booking->date), 'Y-m-d H:i:s O') : format_date($booking->created, 'custom', 'Y-m-d H:i:s O'), '%timezone' => !empty($booking->date) ? date_format(date_create($booking->date), 'O') : format_date($booking->created, 'custom', 'O'))),
    '#default_value' => !empty($booking->date) ? $booking->date : '',
  );

  $form['options'] = array(
    '#type' => 'fieldset',
    '#access' => user_access('bypass bat_booking entities access'),
    '#title' => t('Publishing options'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#group' => 'additional_settings',
    '#attributes' => array(
      'class' => array('booking-form-published'),
    ),
    '#weight' => 95,
  );
  $form['options']['status'] = array(
    '#type' => 'checkbox',
    '#title' => t('Published'),
    '#default_value' => $booking->status,
  );

  $form['actions'] = array(
    '#type' => 'actions',
    '#tree' => FALSE,
  );
  // We add the form's #submit array to this button along with the actual submit
  // handler to preserve any submit handlers added by a form callback_wrapper.
  $submit = array();
  if (!empty($form['#submit'])) {
    $submit += $form['#submit'];
  }
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Booking'),
    '#submit' => $submit + array('bat_booking_edit_form_submit'),
  );
  if (!empty($booking->label) && bat_booking_access('delete', $booking)) {
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete Booking'),
      '#submit' => $submit + array('bat_booking_form_submit_delete'),
      '#weight' => 45,
    );
  }

  // Depending on whether the form is in a popup or a normal page we need to change
  // the behavior of the cancel button.
  if (isset($form_state['ajax']) && $form_state['ajax'] == TRUE) {
    unset($form['actions']['cancel']);
  }
  else {
    $form['actions']['cancel'] = array(
      '#markup' => l(t('Cancel'), 'admin/bat/config/booking'),
      '#weight' => 50,
    );
  }

  $form['#validate'][] = 'bat_booking_edit_form_validate';

  return $form;
}

/**
 * Form API validation callback for the booking form.
 */
function bat_booking_edit_form_validate(&$form, &$form_state) {
  // Notify field widgets to validate their data.
  entity_form_field_validate('bat_booking', $form, $form_state);
}

/**
 * Form API submit callback for the booking form.
 */
function bat_booking_edit_form_submit(&$form, &$form_state) {
  $booking = entity_ui_controller('bat_booking')->entityFormSubmitBuildEntity($form, $form_state);

  $booking->created = !empty($booking->date) ? strtotime($booking->date) : REQUEST_TIME;
  $booking->changed = time();

  if (isset($booking->author_name)) {
    if ($account = user_load_by_name($booking->author_name)) {
      $booking->uid = $account->uid;
    }
    else {
      $booking->uid = 0;
    }
  }

  $booking->save();
  drupal_set_message(t('Booking @label saved', array('@label' => $booking->label)));

  $form_state['redirect'] = 'admin/bat/config/booking';
}

/**
 * Form API submit callback for the delete button.
 */
function bat_booking_form_submit_delete(&$form, &$form_state) {
  if (isset($form_state['ajax'])) {
    bat_booking_delete($form_state['bat_booking']);
    drupal_set_message(t('The booking has been removed'));
    $form_state['booking_deleted'] = TRUE;
  }
  else {
    $destination = array();
    if (isset($_GET['destination'])) {
      $destination = drupal_get_destination();
      unset($_GET['destination']);
    }

    $form_state['redirect'] = array('admin/bat/config/booking/manage/' . $form_state['bat_booking']->booking_id . '/delete', array('query' => $destination));
  }
}

/**
 * Page to add Booking.
 */
function bat_booking_add_page() {
  $controller = entity_ui_controller('bat_booking');
  return $controller->addPage();
}

/**
 * Displays the list of available booking types for booking creation.
 *
 * @ingroup themeable
 */
function theme_bat_booking_add_list($variables) {
  $content = $variables['content'];

  $output = '';
  if ($content) {
    $output = '<dl class="booking-type-list">';
    foreach ($content as $item) {
      $output .= '<dt>' . l($item['title'], $item['href']) . '</dt>';
      $output .= '<dd>' . filter_xss_admin($item['description']) . '</dd>';
    }
    $output .= '</dl>';
  }
  else {
    if (user_access('administer bat_booking_type entities')) {
      $output = '<p>' . t('Bookings cannot be added because you have not created any booking types yet. Go to the <a href="@create-booking-type">booking type creation page</a> to add a new booking type.', array('@create-booking-type' => url('admin/bat/config/booking-types/add'))) . '</p>';
    }
    else {
      $output = '<p>' . t('No booking types have been created yet for you to use.') . '</p>';
    }
  }

  return $output;
}

/**
 * Sets the breadcrumb for administrative BAT pages.
 */
function bat_booking_set_breadcrumb() {
  $breadcrumb = array(
    l(t('Home'), '<front>'),
    l(t('Administration'), 'admin'),
    l(t('BAT'), 'admin/bat'),
    l(t('Configuration'), 'admin/bat/config'),
    l(t('BAT Bookings'), 'admin/bat/config/booking'),
  );

  drupal_set_breadcrumb($breadcrumb);
}
