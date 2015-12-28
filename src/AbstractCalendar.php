<?php

/**
 * @file
 * Class Calendar
 */

namespace Drupal\bat;

use Drupal\bat\EventInterface;
use Drupal\bat\Event;

/**
 * Handles querying and updating state stores
 */
abstract class AbstractCalendar implements CalendarInterface {

  /**
   * The units we are dealing with. If no unit ids set the calendar will return
   * results for date range and all units within that range.
   *
   * @var array
   */
  public $unit_ids;


  /**
   * Granularity
   *
   * Irrespective of the actual values of the start and end dates we need to know
   * what level of granularity the event should be saved as. This is one of daily or
   * hourly.
   */
  public $granularity;

  /**
   * An array holding the names of the day, hour and minute tables we should be retrieving
   * or adding data to.
   * @var
   */
  public $store;

  /**
   * The default value for events. In the event store this is represented by 0 which is then
   * replaced by the default value provided in the constructor.
   *
   * @var
   */
  public $default_value;


  /**
   *@inheritdoc
   */
  public function addEvents($events) {

    $added = TRUE;

    foreach ($events as $event) {
      // Events save themselves so here we cycle through each and return true if all events
      // were saved

      $check = $event->saveEvent($this->store, $this->granularity);

      if ($check == FALSE) {
        $added = FALSE;
        watchdog('BAT', t('Event with @id, start date @start_date and end date @end_date was not added.', array('@id' => $event->value, '@start_date' => $event->startDateToString(), '@end_date' => $event->endDateToString())));
        break;
      } else {
        watchdog('BAT', t('Event with @id, start date @start_date and end date @end_date added.', array('@id' => $event->value, '@start_date' => $event->startDateToString(), '@end_date' => $event->endDateToString())));
      }

    }

    return $added;
  }

  /**
   * Given a start and end time will retrieve events from the defined store.
   *
   * If unit_ids where defined it will filter for those unit ids.
   *
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @return array
   */
  public function getEvents(\DateTime $start_date, \DateTime $end_date) {

    $events = array();

    // We first get events in the itemized format
    $itemized_events = $this->getEventsItemized($start_date, $end_date);

    // We then normalize those events to create Events that get added to an array
    $events = $this->getEventsNormalized($start_date, $end_date, $itemized_events);

    return $events;
  }

  /**
   * Given a start and end time this will return the states units find themselves in for that range.
   *
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @return array
   *  An array of states keyed by unit
   */
  public function getStates(\DateTime $start_date, \DateTime $end_date) {
    $events = $this->getEvents($start_date, $end_date);
    $states = array();
    foreach ($events as $unit => $unit_events) {
      foreach ($unit_events as $event) {
        $states[$unit][$event->getValue()] = $event->getValue();
      }
    }

    return $states;
  }

  /**
   * Given a date range and a set of valid states it will return then units that are withing that
   * set of valid states.
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param $valid_states
   * @return array
   */
  public function getValidUnits(\DateTime $start_date, \DateTime $end_date, $valid_states) {
    $units = array();

    $states = $this->getStates($start_date, $end_date);
    foreach ($states as $unit => $unit_states) {
      // Create an array with just the states
      $current_states = array_keys($unit_states);
      // Compare the current states with the set of valid states
      $remaining_states = array_diff($current_states, $valid_states);
      if (count($remaining_states) == 0 ) {
        // Unit is in a state that is within the set of valid states so add to result set
        $units[$unit] = $unit;
      }
    }

    return $units;
  }


  /**
   * Provides an itemized array of events keyed by the unit_id and divided by day,
   * hour and minute.
   *
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param $store
   * @return array
   */
  public function getEventsItemized(\DateTime $start_date, \DateTime $end_date) {
    // The final events we will return
    $events = array();

    $queries  = $this->buildQueries($start_date, $end_date);

    $results = $this->getEventData($queries);

    $db_events = array();

    // Cycle through day results and setup an event array
    while( $data = $results[BAT_DAY]->fetchAssoc()) {
      // Figure out how many days the current month has
      $temp_date = new \DateTime($data['year'] . "-" . $data['month']);
      $days_in_month = (int)$temp_date->format('t');
      for ($i = 1; $i<=$days_in_month; $i++) {
        $db_events[$data['unit_id']][BAT_DAY][$data['year']][$data['month']]['d' . $i] = $data['d'.$i];
      }
    }

    // With the day events taken care off let's cycle through hours
    while( $data = $results[BAT_HOUR]->fetchAssoc()) {
      for ($i = 0; $i<=23; $i++) {
        $db_events[$data['unit_id']][BAT_HOUR][$data['year']][$data['month']][$data['day']]['h'. $i] = $data['h'.$i];
      }
    }

    // With the hour events taken care off let's cycle through minutes
    while( $data = $results[BAT_MINUTE]->fetchAssoc()) {
      for ($i = 0; $i<=59; $i++) {
        if ($i <= 9) {
          $index = 'm0'.$i;
        } else {
          $index = 'm'.$i;
        }
        $db_events[$data['unit_id']][BAT_MINUTE][$data['year']][$data['month']][$data['day']][$data['hour']][$index] = $data[$index];
      }
    }

    // Create a mock itemized event for the period in question - since event data is either
    // in the database or the default value we first create a mock event and then fill it in
    // accordingly
    $mock_event = new Event($start_date, $end_date, NULL, $this->default_value);
    $itemized = $mock_event->itemizeEvent();

    // Cycle through each unit retrieved and provide it with a fully configured itemized mock event
    foreach ($db_events as $unit => $event) {
      // Add the mock event
      $events[$unit] = $itemized;

      // Fill in month data coming from the database for our event
      foreach ($itemized[BAT_DAY] as $year => $months) {
        foreach ($months as $month => $days) {
          // Check if month is defined in DB otherwise set to default value
          if (isset($db_events[$unit][BAT_DAY][$year][$month])) {
            foreach ($days as $day => $value) {
              $events[$unit][BAT_DAY][$year][$month][$day] = ((int)$db_events[$unit][BAT_DAY][$year][$month][$day] == 0 ? $this->default_value : (int)$db_events[$unit][BAT_DAY][$year][$month][$day]);
            }
          }
          else {
          }

        }
      }

      // Fill in hour data coming from the database for our event that is represented
      // in the mock event
      foreach ($itemized[BAT_HOUR] as $year => $months) {
        foreach ($months as $month => $days) {
          foreach ($days as $day => $hours) {
            foreach ($hours as $hour => $value) {
              if (isset($db_events[$unit][BAT_HOUR][$year][$month][$day][$hour])) {
                $events[$unit][BAT_HOUR][$year][$month]['d' . $day][$hour] = ((int)$db_events[$unit][BAT_DAY][$year][$month][$day][$hour] == 0 ? $this->default_value : (int)$db_events[$unit][BAT_DAY][$year][$month][$day][$hour]);
              }
              else {
                // If nothing from db - then revert to the defaults
                $events[$unit][BAT_HOUR][$year][$month][$day][$hour] = (int)$value;
              }
            }
          }
        }
      }

      // Now fill in hour data coming from the database which the mock event did *not* cater for
      // but the mock event
      foreach ($db_events[$unit][BAT_HOUR] as $year => $months) {
        foreach ($months as $month => $days) {
          foreach ($days as $day => $hours) {
            foreach ($hours as $hour => $value) {
              $events[$unit][BAT_HOUR][$year][$month]['d'.$day][$hour] = ((int)$value == 0 ? $this->default_value : (int)$value);
            }
          }
        }
      }

      // Fill in minute data coming from the database for our event that is represented
      // in the mock event
      foreach ($itemized[BAT_MINUTE] as $year => $months) {
        foreach ($months as $month => $days) {
          foreach ($days as $day => $hours) {
            foreach ($hours as $hour => $minutes) {
              foreach ($minutes as $minute => $value) {
                if (isset($db_events[$unit][BAT_MINUTE][$year][$month][$day][$hour][$minute])) {
                  $events[$unit][BAT_MINUTE][$year][$month]['d' .$day]['h'.$hour][$minute] = ((int)$db_events[$unit][BAT_DAY][$year][$month][$day][$hour][$minute] == 0 ? $this->default_value : (int)$db_events[$unit][BAT_DAY][$year][$month][$day][$hour][$minute]);
                }
                else {
                  // If nothing from db - then revert to the defaults
                  $events[$unit][BAT_MINUTE][$year][$month][$day][$hour][$minute] = (int)$value;
                }
              }
            }
          }
        }
      }

      // Now fill in minute data coming from the database which the mock event did *not* cater for
      foreach ($db_events[$unit][BAT_MINUTE] as $year => $months) {
        foreach ($months as $month => $days) {
          foreach ($days as $day => $hours) {
            foreach ($hours as $hour => $minutes) {
              foreach ($minutes as $minute => $value) {
                $events[$unit][BAT_MINUTE][$year][$month]['d'.$day]['h'.$hour][$minute] = ((int)$value == 0 ? $this->default_value : (int)$value);
              }
            }
          }
        }
      }

    }

    // Check to see if any events came back from the db
    if (count($events) == 0) {
      // If we don't have any db events add mock events (itemized)
      foreach ($this->unit_ids as $unit) {
        $empty_event = new Event($start_date, $end_date, $unit, $this->default_value);
        $events[$unit] = $empty_event->itemizeEvent();
      }
    }

    return $events;
  }

  /**
   * Given an itemized set of event data it will return an array of Events
   *
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param $events
   * @return array
   */
  public function getEventsNormalized(\DateTime $start_date, \DateTime $end_date, $events) {

    $normalized_events = array();

    $events_copy = $events;

    foreach ($events_copy as $unit => $data) {

      // Make sure years are sorted
      ksort($data[Event::BAT_DAY]);
      ksort($data[Event::BAT_HOUR]);
      ksort($data[Event::BAT_MINUTE]);

      // Set up variables to keep track of stuff
      $current_value = NULL;
      $start_event = NULL;
      $end_event = NULL;
      $event_value = NULL;
      $last_day = NULL;
      $last_hour = NULL;
      $last_minute = NULL;

      foreach ($data[Event::BAT_DAY] as $year => $months) {
        // Make sure months are in right order
        ksort($months);
        foreach ($months as $month => $days) {
          foreach ($days as $day => $value) {
            if ($value == -1) {
              // Retrieve hour data
              $hour_data = $events[$unit][Event::BAT_HOUR][$year][$month][$day];
              ksort($hour_data, SORT_NATURAL);
              foreach ($hour_data as $hour => $hour_value) {
                if ($hour_value == -1) {
                  // We are going to need minute values
                  $minute_data = $events[$unit][Event::BAT_MINUTE][$year][$month][$day][$hour];
                  ksort($minute_data, SORT_NATURAL);
                  foreach ($minute_data as $minute => $minute_value) {
                    if ($current_value === $minute_value) {
                      // We are still in minutes and going through so add a minute
                      $end_event->add(new \DateInterval('PT1M'));
                    } elseif (($current_value != $minute_value) && ($current_value !== NULL)) {
                      // Value just switched - let us wrap up with current event and start a new one
                      $normalized_events[$unit][] = new Event($start_event, $end_event, $unit, $current_value);
                      $start_event = clone($end_event->add(new \DateInterval('PT1M')));
                      $end_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . substr($hour, 1) . ':' . substr($minute,1));
                      $current_value = $minute_value;
                    }
                    if ($current_value === NULL) {
                      // We are down to minutes and haven't created and even yet - do one now
                      $start_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . substr($hour, 1) . ':' . substr($minute,1));
                      $end_event = clone($start_event);
                    }
                    $current_value = $minute_value;
                  }
                } elseif ($current_value === $hour_value) {
                  // We are in hours and can add something
                  $end_event->add(new \DateInterval('PT1H'));
                } elseif (($current_value != $hour_value) && ($current_value !== NULL)) {
                  // Value just switched - let us wrap up with current event and start a new one
                  $normalized_events[$unit][] = new Event($start_event, $end_event, $unit, $current_value);
                  // Start event becomes the end event with a minute added
                  $start_event = clone($end_event->add(new \DateInterval('PT1M')));
                  // End event comes the current point in time
                  $end_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . substr($hour, 1) . ':00');
                  $current_value = $hour_value;
                }
                if ($current_value === NULL) {
                  // Got into hours and still haven't created an event so
                  $start_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . substr($hour, 1) . ':00');
                  // We will be occupying at least this hour so might as well mark it
                  $end_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . substr($hour, 1) . ':59');
                  $current_value = $hour_value;
                }
              }
            } elseif ($current_value === $value) {
              // We are adding a whole day so the end event gets moved to the end of the day we are adding
              $end_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . '23:59');
            } elseif (($current_value !== $value) && ($current_value !== NULL)) {
              // Value just switched - let us wrap up with current event and start a new one
              $normalized_events[$unit][] = new Event($start_event, $end_event, $unit, $current_value);
              // Start event becomes the end event with a minute added
              $start_event = clone($end_event->add(new \DateInterval('PT1M')));
              // End event becomes the current day which we have not account for yet
              $end_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . '23:59');
              $current_value = $value;
            }
            if ($current_value === NULL) {
              // We have not created an event yet so let's do it now
              $start_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . '00:00');
              $end_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . '23:59');
              $current_value = $value;
            }
          }
        }
      }

      // Add the last event in for which there is nothing in the loop to catch it
      $normalized_events[$unit][] = new Event($start_event, $end_event, $unit, $current_value);
    }

    // Given the database structure we may get events that are not with the date ranges we were looking for
    // We get rid of them here so that the user has a clean result.
    foreach ($normalized_events as $unit => $events) {
      foreach ($events as $key => $event) {
        if ($event->inRange($start_date, $end_date)) {
          // Adjust start or end dates of events so everything is within range
          if ($event->startsEarlier($start_date)) {
            $event->setStartDate($start_date);
          }
          if ($event->endsLater($end_date)) {
            $event->setEndDate($end_date);
          }
        }
        else {
          // Event completely not in range so unset it
          unset($normalized_events[$unit][$key]);
        }
      }
    }

    return $normalized_events;
  }


  /**
   * Given a set of queries (see buildQueries) it will use Drupal's db_query to get results.
   *
   * @param $queries
   * @return array
   */
  private function getEventData($queries) {
    $results = array();
    // Run each query and store results
    foreach ($queries as $type => $query) {
      $results[$type] = db_query($query);
    }

    return $results;
  }

  /**
   * Constructs the appropriate queries for a specific store.
   *
   * @param $start_date
   * @param $end_date
   * @param $store
   * @return array
   */
  public function buildQueries($start_date, $end_date) {
    $queries = array();

    $queries[BAT_DAY] = 'SELECT * FROM ' . $this->store[Event::BAT_DAY] . ' WHERE ';
    $queries[BAT_HOUR] = 'SELECT * FROM ' . $this->store[Event::BAT_HOUR] . ' WHERE ';
    $queries[BAT_MINUTE] = 'SELECT * FROM ' . $this->store[Event::BAT_MINUTE] . ' WHERE ';

    $hours_query = TRUE;
    $minutes_query = TRUE;

    // Create a mock event which we will use to determine how to query the database
    $mock_event = new Event($start_date, $end_date, 0, -10);
    // We don't need a granular event even if we are retrieving granular data - since we don't
    // know what the event break-down is going to be we need to get the full range of data from
    // days, hours and minutes.
    $itemized = $mock_event->itemizeEvent(BAT_DAILY);

    $year_count = 0;
    $hour_count = 0;
    $minute_count = 0;

    $query_parameters = '';

    foreach($itemized[BAT_DAY] as $year => $months) {
      if ($year_count > 0) {
        // We are dealing with multiple years so add an OR
        $query_parameters .= ' OR ';
      }
      $query_parameters .= 'year IN (' . $year . ') ';
      $query_parameters .= 'AND month IN (' . implode("," ,array_keys($months)) .') ';
      if (count($this->unit_ids) > 0) {
        // Unit ids are defined so add this as a filter
        $query_parameters .= 'AND unit_id in (' . implode("," , $this->unit_ids) .') ';
      }
      $year_count++;
    }
    // Add parameters to each query

    $queries[BAT_DAY] .= $query_parameters;
    $queries[BAT_HOUR] .= $query_parameters;
    $queries[BAT_MINUTE] .= $query_parameters;

    // Clean up and add ordering information
    $queries[BAT_DAY] .= ' ORDER BY unit_id, year, month';
    $queries[BAT_HOUR] .= ' ORDER BY unit_id, year, month, day';
    $queries[BAT_MINUTE] .= ' ORDER BY unit_id, year, month, day, hour';

    return $queries;
  }

  /**
   * A simple utility funciton that given an array of datum=>value will group results based on
   * those that have the same value. Useful for grouping events based on state.
   *
   * @param $data
   * @param $length
   */
  public function groupData($data, $length) {
    // Given an array of the structure $date => $value we create another array
    // of structure $event, $length, $value
    // Cycle through day data and create events
    $flipped = array();
    $e = 0;
    $j = 0;
    $old_value = NULL;

    foreach ($data as $datum => $value) {
      $j++;
      if ($j <= $length) {
        // If the value has changed and we are not just starting
        if (($value != $old_value)) {
          $e++;
          $flipped[$e][$value][$datum] = $datum;
          $old_value = $value;
        }
        else {
          $flipped[$e][$value][$datum] = $datum;
        }
      }
    }

  }

}
