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
   * Booking Mode (daily/hourly).
   *
   * @var string
   */
  public $booking_mode;

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
  public function __construct($unit_id, $event_id, $start_date, $end_date, $booking_mode = 'daily') {
    $this->unit_id = $unit_id;
    $this->id = $event_id;
    $this->start_date = $start_date;
    $this->end_date = $end_date;
    $this->booking_mode = $booking_mode;
  }

  /**
   * {@inheritdoc}
   */
  protected function createEvent(\DateTime $start_date, \DateTime $end_date) {
    return new BookingEvent($this->unit_id, $this->id, $start_date, $end_date);
  }

  /**
   * Returns the event id.
   *
   * @return int
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Set the event id.
   *
   * @param int $event_id
   */
  public function setId($event_id) {
    $this->id = $event_id;
  }

  /**
   * Returns the booking mode (daily/hourly).
   *
   * @return string
   */
  public function getBookingMode() {
    return $this->booking_mode;
  }

  /**
   * Set the booking mode (daily/hourly).
   *
   * @param string $booking_mode
   */
  public function setBookingMode($booking_mode) {
    $this->booking_mode = $booking_mode;
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
      $booking = bat_event_load($booking_id);
      if ($style == BAT_AVAILABILITY_ADMIN_STYLE) {
        $event['title'] = t('Booking') . ': ' . $booking->booking_id;
      }
      elseif ($style == BAT_AVAILABILITY_GENERIC_STYLE) {
        $this->id = BAT_NOT_AVAILABLE;
      }
    }

    $event_states = bat_event_get_states();

    if (isset($event_states[$this->id])) {
      $event['color'] = $event_states[$this->id]['color'];
      $event['title'] = $event_states[$this->id]['calendar_label'];
    }
    else {
      $event['color'] = '#017eba';
    }

    if (variable_get('bat_view_unit_name', '')) {
      $event['title'] = $unit_name;
    }

    return $event;
  }

}
