<?php

/**
 * @file
 * Contains \Drupal\bat_availability\BatCalendarInterface.
 */

namespace Drupal\bat_availability;

use Drupal\bat_availability\BatCalendarInterface;
use Drupal\bat_availability\BatEventInterface;

/**
 *
 */
class BatCalendarController implements BatCalendarControllerInterface {
  /**
   *
   */
  public function __construct() {

  }

  /**
   *
   */
  public function saveEvent(BatEventInterface $event) {
    $daily_array = $this->transformToDaily($event);
    $hourly_array = $this->transformToHourly($event);
    $minute_array = $this->transformToMinute($event);

    foreach ($daily_array as $year => $year_array) {
      foreach ($year_array as $month => $month_array) {
        $keys = array('unit_id' => 1, 'year' => $year, 'month' => $month);
        $fields = $keys + $month_array;

        db_merge('availability_events_day_state')
          ->keys($keys)
          ->fields($fields)
          ->execute();
      }
    }

    foreach ($hourly_array as $year => $year_array) {
      foreach ($year_array as $month => $month_array) {
        foreach ($month_array as $day => $day_array) {
          $keys = array('unit_id' => 1, 'year' => $year, 'month' => $month, 'day' => $day);
          $fields = $keys + $day_array;

          db_merge('availability_events_hour_state')
            ->keys($keys)
            ->fields($fields)
            ->execute();
        }
      }
    }

    foreach ($minute_array as $year => $year_array) {
      foreach ($year_array as $month => $month_array) {
        foreach ($month_array as $day => $day_array) {
          foreach ($day_array as $hour => $hour_array) {
            $keys = array('unit_id' => 1, 'year' => $year, 'month' => $month, 'day' => $day, 'hour' => $hour);
            $fields = $keys + $hour_array;

            db_merge('availability_events_minute_state')
              ->keys($keys)
              ->fields($fields)
              ->execute();
          }
        }
      }
    }
  }

  /**
   *
   */
  public function updateEvent(BatEventInterface $event) {

  }

  /**
   *
   */
  public function deleteEvent(BatEventInterface $event) {

  }

  /**
   *
   */
  protected function transformToDaily(BatEventInterface $event) {
    $daily_array = array();

    $state = $event->getStateInteger();
    $start_date = $event->getStartDate();
    $end_date = $event->getEndDate();

    $interval = \DateInterval::createFromDateString('1 day');
    $period = new \DatePeriod($start_date, $interval, $end_date);

    foreach($period as $dt) {
      if ( ($dt->format('Y') == $start_date->format('Y') &&
            $dt->format('m') == $start_date->format('m') &&
            $dt->format('d') == $start_date->format('d')) ||
           ($dt->format('Y') == $end_date->format('Y') &&
            $dt->format('m') == $end_date->format('m') &&
            $dt->format('d') == $end_date->format('d')) ) {
        $daily_array[$dt->format('Y')][$dt->format('m')]['d' . $dt->format('j')] = 0;
      }
      else {
        $daily_array[$dt->format('Y')][$dt->format('m')]['d' . $dt->format('j')] = $state;
      }
    }

    $daily_array[$end_date->format('Y')][$end_date->format('m')]['d' . $end_date->format('j')] = 0;

    return $daily_array;
  }

  /**
   *
   */
  protected function transformToHourly(BatEventInterface $event) {
    $hourly_array = array();

    $state = $event->getStateInteger();

    $start_date = $event->getStartDate();
    $start_date1 = new \DateTime($start_date->format('Y') . '-' . $start_date->format('m') . '-' . $start_date->format('d') . ' 24:00');

    $end_date = $event->getEndDate();
    $end_date1 = new \DateTime($end_date->format('Y') . '-' . $end_date->format('m') . '-' . $end_date->format('d') . ' 00:00');

    $interval = \DateInterval::createFromDateString('1 hour');

    $period = new \DatePeriod($start_date, $interval, $start_date1);
    foreach($period as $dt) {
      if ($dt->format('Y') == $start_date->format('Y') &&
          $dt->format('m') == $start_date->format('m') &&
          $dt->format('d') == $start_date->format('d') &&
          $dt->format('G') == $start_date->format('G')) {
        $hourly_array[$dt->format('Y')][$dt->format('m')][$dt->format('j')]['h' . ($dt->format('G') + 1)] = 0;
      }
      else {
        $hourly_array[$dt->format('Y')][$dt->format('m')][$dt->format('j')]['h' . ($dt->format('G') + 1)] = $state;
      }
    }

    $period = new \DatePeriod($end_date1, $interval, $end_date);
    foreach($period as $dt) {
      if ($dt->format('Y') == $end_date->format('Y') &&
          $dt->format('m') == $end_date->format('m') &&
          $dt->format('d') == $end_date->format('d') &&
          $dt->format('G') == $end_date->format('G')) {
        $hourly_array[$dt->format('Y')][$dt->format('m')][$dt->format('j')]['h' . ($dt->format('G') + 1)] = 0;
      }
      else {
        $hourly_array[$dt->format('Y')][$dt->format('m')][$dt->format('j')]['h' . ($dt->format('G') + 1)] = $state;
      }
    }

    return $hourly_array;
  }

  /**
   *
   */
  protected function transformToMinute(BatEventInterface $event) {
    $minute_array = array();

    $state = $event->getStateInteger();

    $start_date = $event->getStartDate();
    $start_date1 = new \DateTime($start_date->format('Y') . '-' . $start_date->format('m') . '-' . $start_date->format('d') . ' ' . ((int)$start_date->format('H') + 1) . ':00');

    $end_date = $event->getEndDate();
    $end_date1 = new \DateTime($end_date->format('Y') . '-' . $end_date->format('m') . '-' . $end_date->format('d') . ' ' . $end_date->format('H') . ':00');

    $interval = \DateInterval::createFromDateString('1 minute');

    $period = new \DatePeriod($start_date, $interval, $start_date1);
    foreach($period as $dt) {
      $minute_array[$dt->format('Y')][$dt->format('m')][$dt->format('j')][$dt->format('G')]['m' . ((int)$dt->format('i') + 1)] = $state;
    }

    $period = new \DatePeriod($end_date1, $interval, $end_date);
    foreach($period as $dt) {
      $minute_array[$dt->format('Y')][$dt->format('m')][$dt->format('j')][$dt->format('G')]['m' . ((int)$dt->format('i') + 1)] = $state;
    }

    return $minute_array;
  }
}
