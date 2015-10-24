<?php

/**
 * @file
 * Class BookingEvent
 */

namespace Drupal\bat_availability;

use Drupal\bat\BatEventInterface;
use Drupal\bat\BatEvent;

class BookingEvent extends BatEvent implements BookingEventInterface {

  /**
   * The type of event.
   *
   * @var int
   */
  public $id;

  /**
   * Constructs a BookingEvent instance.
   *
   * @param int $unit_id
   *   The bookable unit.
   * @param int $event_id
   *   The event ID.
   * @param DateTime $start_date
   *   The start date of the event.
   * @param DateTime $end_date
   *   The end date of the event.
   */
  public function __construct($unit_id, $event_id, $start_date, $end_date) {
    $this->unit_id = $unit_id;
    $this->id = $event_id;
    $this->start_date = $start_date;
    $this->end_date = $end_date;
  }

  /**
   * {@inheritdoc}
   */
  protected function createEvent(\DateTime $start_date, \DateTime $end_date) {
    return new BookingEvent($this->unit_id, $this->id, $start_date, $end_date);
  }

  /**
   * {@inheritdoc}
   */
  public function lock() {
    // Check that the event is not already locked.
    $query = db_select('bat_booking_locks', 'l');
    $query->addField('l', 'unit_id');
    $query->addField('l', 'state');
    $query->addField('l', 'locked');
    $query->condition('l.unit_id', $this->unit_id);
    $query->condition('l.state', $this->id);
    $query->condition('l.locked', 1);
    $result = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

    if (count($result) == 1) {
      return FALSE;
    }
    else {
      $fields = array(
        'unit_id' => $this->unit_id,
        'state' => $this->id,
        'locked' => 1,
      );
      $lock = db_insert('bat_booking_locks')->fields($fields);
      $lock->execute();
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function unlock() {
    db_delete('bat_booking_locks')
    ->condition(db_or()->condition('state', $this->id)->condition('state', -($this->id)))
    ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function formatJson($style = BAT_AVAILABILITY_ADMIN_STYLE, $unit_name = '') {
    $event = array(
      'id' => $this->id,
      'start' => $this->startYear() . '-' . $this->startMonth('m') . '-' . $this->startDay('d') . 'T13:00:00Z',
      'end' => $this->endYear() . '-' . $this->endMonth('m') . '-' . $this->endDay('d') . 'T13:00:00Z',
      'title' => $this->id,
    );

    // Check if we are dealing with a booking.
    if ($this->id > 10 || $this->id < -10) {
      // Get the actual booking id.
      $booking_id = bat_availability_return_id($this->id);
      $booking = bat_booking_load($booking_id);
      if ($style == BAT_AVAILABILITY_ADMIN_STYLE) {
        $event['title'] = t('Booking') . ': ' . $booking->booking_id;
      }
      elseif ($style == BAT_AVAILABILITY_GENERIC_STYLE) {
        $this->id = BAT_NOT_AVAILABLE;
      }
    }

    $view_unit_name = array_filter(variable_get('bat_view_unit_name', array('')));

    // Set the color.
    switch ($this->id) {
      case BAT_NOT_AVAILABLE:
        $event['color']  = variable_get('bat_not_available_color', '#CC2727');
        $event['title'] = variable_get('bat_not_available_text', 'N/A');
        break;

      case BAT_AVAILABLE:
        $event['color'] = variable_get('bat_available_color', '#8BA175');
        $event['title'] = variable_get('bat_available_text', 'AV');
        break;

      case BAT_ON_REQUEST:
        $event['color'] = variable_get('bat_on_request_color', '#C5C5C5');
        $event['title'] = variable_get('bat_on_request_text', 'ON-REQ');
        break;

      case BAT_HOURLY_BOOKED:
        $event['color'] = '#017eba';
        $event['title'] = 'Hourly bookings';
        break;

      case ($this->id < 0):
        $event['color'] = variable_get('bat_unconfirmed_booking_color', '#6D8C9C');
        $event['title'] = variable_get('bat_unconfirmed_booking_text', 'UNCONF');
        break;

      case BAT_ANON_BOOKED:
        if ($style == BAT_AVAILABILITY_ADMIN_STYLE) {
          $event['color'] = variable_get('bat_anon_booking_color', '#8C6A5A');
          $event['title'] = variable_get('bat_anon_booking_text', 'A-B');
        }
        elseif ($style == BAT_AVAILABILITY_GENERIC_STYLE) {
          $event['color']  = variable_get('bat_not_available_color',
            '#910a1c');
          $event['title'] = variable_get('bat_not_available_text', 'N/A');
        }
        break;

      default:
        $event['color'] = '#017eba';
    }

    if (!empty($view_unit_name)) {
      $event['title'] = $unit_name;
    }

    return $event;
  }

}
