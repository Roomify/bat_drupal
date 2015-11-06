<?php

/**
 * @file
 * Class BatDateInterval.
 */

namespace Drupal\bat_hourly_availability;

/**
 * A set of static helper functions for DateInterval.
 */
class BatDateInterval {

  /**
   * Compare two DateInterval.
   *
   * @param DateInterval $interval1
   * @param DateInterval $interval2
   *
   * @return integer
   */
  public static function compare(\DateInterval $interval1, \DateInterval $interval2) {
    $seconds_interval1 = BatDateInterval::ConvertToSeconds($interval1);
    $seconds_interval2 = BatDateInterval::ConvertToSeconds($interval2);

    if ($seconds_interval1 < $seconds_interval2) {
      return -1;
    }
    elseif ($seconds_interval1 == $seconds_interval2) {
      return 0;
    }

    return 1;
  }

  /**
   * Return the total number of seconds in a DateInterval.
   *
   * @param DateInterval $interval
   *
   * @return integer
   */
  public static function convertToSeconds(\DateInterval $interval) {
    $seconds = $interval->s + ($interval->i * 60) + ($interval->h * 3600);

    if ($interval->days > 0) {
      $seconds += ($interval->days * 86400);
    }
    else {
      $seconds += ($interval->d * 86400) + ($interval->m * 2592000) + ($interval->y * 31536000);
    }

    if ($interval->invert) {
      $seconds *= -1;
    }

    return $seconds;
  }

  /**
   * Formats a DateInterval.
   *
   * @param DateInterval $interval
   *
   * @return string
   */
  public static function format(\DateInterval $interval) {
    $format = array();

    if ($interval->d > 0) {
      $format[] = ($interval->d == 1) ? $interval->format('%d day') : $interval->format('%d days');
    }
    if ($interval->h > 0) {
      $format[] = ($interval->h == 1) ? $interval->format('%h hour') : $interval->format('%h hours');
    }
    if ($interval->i > 0) {
      $format[] = ($interval->i == 1) ? $interval->format('%i minute') : $interval->format('%i minutes');
    }
    if ($interval->s > 0) {
      $format[] = ($interval->s == 1) ? $interval->format('%s second') : $interval->format('%s seconds');
    }

    return implode(' ', $format);
  }

  /**
   * Sum two DateInterval.
   *
   * @param DateInterval $interval1
   * @param DateInterval $interval2
   *
   * @return DateInterval
   */
  public static function sum(\DateInterval $interval1, \DateInterval $interval2) {
    $interval = new \DateInterval($interval1->format('P%yY%dDT%hH%iM%sS'));

    foreach (str_split('ymdhis') as $prop) {
      $interval->$prop += $interval2->$prop;
    }

    $interval->i += (int)($interval->s / 60);
    $interval->s = $interval->s % 60;
    $interval->h += (int)($interval->i / 60);
    $interval->i = $interval->i % 60;

    return $interval;
  }

  /**
   * @param DateInterval $interval1
   * @param DateInterval $interval2
   *
   * @return array
   */
  public static function getIntervals(\DateInterval $duration, \DateInterval $max_duration) {
    $duration_options[BatDateInterval::format($duration)] = BatDateInterval::format($duration);

    $interval = BatDateInterval::sum($duration, $duration);

    if (BatDateInterval::compare($interval, $max_duration) == -1) {
      $duration_options[BatDateInterval::format($interval)] = BatDateInterval::format($interval);

      while (BatDateInterval::compare($interval, $max_duration) == -1) {
        $interval = BatDateInterval::sum($interval, $duration);
        $duration_options[BatDateInterval::format($interval)] = BatDateInterval::format($interval);
      }
    }

    return $duration_options;
  }

}
