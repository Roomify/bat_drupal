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

/**
 *
 */
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
    $form['start_date'] = [
      '#type' => 'hidden',
      '#value' => $start_date->format('Y-m-d'),
    ];

    $form['end_date'] = [
      '#type' => 'hidden',
      '#value' => $end_date->format('Y-m-d'),
    ];

    $form['type_id'] = [
      '#type' => 'hidden',
      '#value' => $type_id,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Confirm booking',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $event_type = 'availability_example';

    $start_date = new \DateTime($values['start_date']);
    $end_date = new \DateTime($values['end_date']);
    $end_date->sub(new \DateInterval('PT1M'));

    $type_id = $values['type_id'];

    $state_ids = array_keys(bat_event_get_states($event_type));

    $state_store = new DrupalDBStore($event_type, DrupalDBStore::BAT_STATE);

    $valid_states = array_merge([0], array_slice($state_ids, 0, 1));

    $drupal_units = bat_unit_load_multiple(FALSE, ['unit_type_id' => $type_id]);
    $bat_units = [];
    foreach ($drupal_units as $unit_id => $unit) {
      $bat_units[] = new Unit($unit_id, $unit->getEventDefaultValue($event_type));
    }

    if (count($bat_units)) {
      $calendar = new Calendar($bat_units, $state_store);

      $response = $calendar->getMatchingUnits($start_date, $end_date, $valid_states, []);
      $valid_unit_ids = array_keys($response->getIncluded());

      if (count($valid_unit_ids)) {
        // Create a new Event.
        $event = bat_event_create([
          'type' => $event_type,
          'start_date' => $start_date->format('Y-m-d H:i:s'),
          'end_date' => $end_date->format('Y-m-d H:i:s'),
          'uid' => $this->currentUser()->id(),
        ]);

        $event->set('event_bat_unit_reference', reset($valid_unit_ids));
        $event->set('event_state_reference', end($state_ids));

        $event->save();

        // Create a new Booking.
        $booking = bat_booking_create([
          'type' => 'standard',
          'label' => 'Example Booking',
        ]);

        $booking->set('booking_start_date', $start_date->format('Y-m-d H:i:s'));
        $booking->set('booking_end_date', $end_date->format('Y-m-d H:i:s'));
        $booking->set('booking_event_reference', $event->id());

        $booking->save();

        $this->messenger()->addMessage(t('Booking created'));
      }
      else {
        $this->messenger()->addError(t('No units'));
      }
    }
  }

}
