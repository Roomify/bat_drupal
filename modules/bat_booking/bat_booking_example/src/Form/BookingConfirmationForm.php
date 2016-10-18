<?php

/**
 * @file
 * Contains \Drupal\bat_booking_example\Form\BookingConfirmationForm.
 */

namespace Drupal\bat_booking_example\Form;

use Roomify\Bat\Calendar\Calendar;
use Roomify\Bat\Event\Event;
use Roomify\Bat\Store\DrupalDBStore;
use Roomify\Bat\Unit\Unit;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class BookingConfirmationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bat_booking_confirmation_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $start_date = NULL, $end_date = NULL, $type_id = NULL) {
    $form['start_date'] = array(
      '#type' => 'hidden',
      '#value' => $start_date,
    );

    $form['end_date'] = array(
      '#type' => 'hidden',
      '#value' => $end_date,
    );

    $form['type_id'] = array(
      '#type' => 'hidden',
      '#value' => $type_id,
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Confirm booking', 
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $event_type = 'availability_example';

    $start_date = $values['start_date'];
    $end_date = $values['end_date'];
    $end_date->sub(new DateInterval('PT1M'));

    $type_id = $values['type_id'];

    $state_ids = array_keys(bat_event_get_states($event_type));

    $state_store = new DrupalDBStore($event_type, DrupalDBStore::BAT_STATE);

    $valid_states = array_merge(array(0), array_slice($state_ids, 0, 1));

    $drupal_units = bat_unit_load_multiple(FALSE, array('type_id' => $type_id));
    $bat_units = array();
    foreach ($drupal_units as $unit_id => $unit) {
      $bat_units[] = new Unit($unit_id, $unit->getEventDefaultValue($event_type));
    }

    if (count($bat_units)) {
      $calendar = new Calendar($bat_units, $state_store);

      $response = $calendar->getMatchingUnits($start_date, $end_date, $valid_states, array());
      $valid_unit_ids = array_keys($response->getIncluded());

      if (count($valid_unit_ids)) {
        // Create a new Event.
        $event = bat_event_create(array(
          'type' => $event_type,
          'start_date' => $start_date->format('Y-m-d H:i:s'),
          'end_date' => $end_date->format('Y-m-d H:i:s'),
          'uid' => \Drupal::currentUser()->id(),
        ));

        $event->event_bat_unit_reference[LANGUAGE_NONE][0]['target_id'] = reset($valid_unit_ids);
        $event->event_state_reference[LANGUAGE_NONE][0]['state_id'] = end($state_ids);

        $event->save();

        // Create a new Booking.
        $booking = bat_booking_create(array(
          'type' => 'standard',
          'label' => 'Example Booking',
        ));

        $booking->booking_start_date[LANGUAGE_NONE][0]['value'] = $start_date->format('Y-m-d H:i:s');
        $booking->booking_end_date[LANGUAGE_NONE][0]['value'] = $end_date->format('Y-m-d H:i:s');
        $booking->booking_event_reference[LANGUAGE_NONE][0]['target_id'] = $event->event_id;

        $booking->save();

        drupal_set_message(t('Booking created'));
      }
      else {
        drupal_set_message(t('No units'), 'error');
      }
    }
  }

}
