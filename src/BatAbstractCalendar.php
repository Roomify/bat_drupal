<?php

/**
 * @file
 * Class BatCalendar
 */

namespace Drupal\bat;

use Drupal\bat\BatGranularEventInterface;
use Drupal\bat\BatGranularEvent;


define('BAT_EVENT_DAY_EVENT', 'bat_event_day_event');
define('BAT_EVENT_DAY_STATE', 'bat_event_day_state');
define('BAT_EVENT_HOUR_EVENT', 'bat_event_hour_event');
define('BAT_EVENT_HOUR_STATE', 'bat_event_hour_state');
define('BAT_EVENT_MINUTE_EVENT', 'bat_event_minute_event');
define('BAT_EVENT_MINUTE_STATE', 'bat_event_minute_state');

/**
 * Handles querying and updating the availability information
 * relative to a single bookable unit based on BAT's data structure
 */
abstract class BatAbstractCalendar implements BatCalendarInterface {

  /**
   * The units we are dealing with. If no unit ids set the calendar will return
   * results for date range and all units within that range.
   *
   * @var array
   */
  protected $unit_ids;

  /**
   * The default value for state or event
   *
   * @var int
   */
  protected $default_state;

  /**
   * Granularity
   *
   * Irrespective of the actual values of the start and end dates we need to know
   * what level of granularity the event should be saved as. This is one of daily or
   * hourly.
   */
  protected $granularity;

  public $start_date;

  public $end_date;

  /**
   * {@inheritdoc}
   */
  public function updateCalendar($events, $remove = FALSE) {

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
  public function getEventsItemized(\DateTime $start_date, \DateTime $end_date, $store) {
    // The final events we will return
    $events = array();

    $queries  = $this->buildQueries($start_date, $end_date, $store);
    $results = $this->getEventData($queries);

    $db_events = array();

    // Cycle through day results and setup an event array
    while( $data = $results[BAT_DAY]->fetchAssoc()) {
      // Figure out how many days the current month has
      $temp_date = new \DateTime($data['year'] . "-" . $data['month']);
      $days_in_month = (int)$temp_date->format('t');
      for ($i = 1; $i<=$days_in_month; $i++){
        $db_events[$data['unit_id']][BAT_DAY][$data['year']][$data['month']]['d' . $i] = $data['d'.$i];
      }
    }

    // With the day events taken care off let's cycle through hours
    while( $data = $results[BAT_HOUR]->fetchAssoc()) {
      for ($i = 0; $i<=23; $i++){
        $db_events[$data['unit_id']][BAT_HOUR][$data['year']][$data['month']][$data['day']]['h'. $i] = $data['h'.$i];
      }
    }

    // With the hour events taken care off let's cycle through minutes
    while( $data = $results[BAT_MINUTE]->fetchAssoc()) {
      for ($i = 0; $i<=59; $i++){
        if ($i <= 9){
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
    $mock_event = new BatGranularEvent($start_date, $end_date);
    $itemized = $mock_event->itemizeEvent();

    // Cycle through each unit retrieved and provide it with a fully configured itemized mock event
    foreach ($db_events as $unit => $event){
      // Add the mock event
      $events[$unit] = $itemized;

      // Fill in month data coming from the database for our event
      foreach ($itemized[BAT_DAY] as $year => $months) {
        foreach ($months as $month => $days) {
          // Check if month is defined in DB otherwise set to default value
          if (isset($db_events[$unit][BAT_DAY][$year][$month])) {
            foreach ($days as $day => $value) {
              $events[$unit][BAT_DAY][$year][$month][$day] = (int)$db_events[$unit][BAT_DAY][$year][$month][$day];
            }
          } else {
          }

        }
      }

      // Fill in hour data coming from the database for our event that is represented
      // in the mock event
      foreach ($itemized[BAT_HOUR] as $year => $months){
        foreach ($months as $month => $days) {
          foreach ($days as $day => $hours) {
            foreach ($hours as $hour => $value) {
              if (isset($db_events[$unit][BAT_HOUR][$year][$month][$day][$hour])) {
                $events[$unit][BAT_HOUR][$year][$month]['d' . $day][$hour] = (int)$db_events[$unit][BAT_DAY][$year][$month][$day][$hour];
                //dpm($events[$unit][BAT_HOUR][$year][$month][$day], 'added_day');
              }
              else {
                // If nothing from db - then revert to the defaults
                $events[$unit][BAT_HOUR][$year][$month][$day][$hour] = $value;
              }
            }
          }
        }
      }

      // Now fill in hour data coming from the database which the mock event did *not* cater for
      foreach ($db_events[$unit][BAT_HOUR] as $year => $months) {
        foreach ($months as $month => $days) {
          foreach ($days as $day => $hours) {
            foreach ($hours as $hour => $value) {
              $events[$unit][BAT_HOUR][$year][$month]['d'.$day][$hour] = (int)$value;
            }
          }
        }
      }

      // Fill in minute data coming from the database for our event that is represented
      // in the mock event
      foreach ($itemized[BAT_MINUTE] as $year => $months){
        foreach ($months as $month => $days) {
          foreach ($days as $day => $hours) {
            foreach ($hours as $hour => $minutes) {
              foreach ($minutes as $minute => $value) {
                if (isset($db_events[$unit][BAT_MINUTE][$year][$month][$day][$hour][$minute])) {
                  $events[$unit][BAT_MINUTE][$year][$month]['d' .$day]['h'.$hour][$minute] = (int)$db_events[$unit][BAT_DAY][$year][$month][$day][$hour][$minute];
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
                $events[$unit][BAT_MINUTE][$year][$month]['d'.$day]['h'.$hour][$minute] = (int)$value;
              }
            }
          }
        }
      }

    }

    return $events;
  }

  /**
   * Provides events keyed by unit_id as a set of starttime, endtime and state the unit is in during that time period
   *
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param $store
   */
  public function getEventDates(\DateTime $start_date, \DateTime $end_date, $store) {

    // Create db queries based on the start and end date
    $queries  = $this->buildQueries($start_date, $end_date, $store);

    // With the queries in place go get the data
    $results = $this->getEventData($queries);


    $events = array();

    // Initialize variables we will need to keep track of things
    $previous_unit = 0;
    $previous_month = 0;
    $previous_year = 0;
    $last_start_day = 0;
    $current_state = 0;
    $days_in_month = 0;

    // Cycle through day results and setup an event array
    while( $data = $results[BAT_DAY]->fetchAssoc()) {
      $current_data = $data;
      $current_unit = $data['unit_id'];
      $current_year = $data['year'];
      $current_month = $data['month'];

      // Reset the start state if we are dealing with a new unit
      if ($previous_unit == 0 || $previous_unit != $current_unit) {
        $start_state = $data['d1'];
        $event_start_day = new \DateTime($current_year . "-" . $current_month . "-1");
      }
      // We are in the cycle but switched units so there is an event to close
      if ($previous_unit !=0 && $previous_unit != $current_unit) {
        $events[$previous_unit][$last_start_day->format('Ynd')] = array($last_start_day, new \DateTime($previous_year . "-" . $previous_month . "-" . $days_in_month), $start_state);
      }

      // Figure out how many days the current month has
      $temp_date = new \DateTime($current_year . "-" . $current_month);
      $days_in_month = (int)$temp_date->format('t');

      for ($i = 1; $i<=$days_in_month; $i++){
        $current_state = $data['d'.$i];


        if ($start_state != $current_state) {
          // We switched states so add event and reset tracking variables
          $event_end_day = new \DateTime($current_year. "-" . $current_month . "-" . ($i-1));
          $events[$current_unit][$event_start_day->format('Ynd')] = array($event_start_day, $event_end_day, $start_state);
          $start_state = $current_state;
          $event_start_day = new \DateTime($current_year. "-" . $current_month . "-" .$i);
          $last_start_day = $event_start_day;
        }
      }

      $previous_unit = $current_unit;
      $previous_month = $current_month;
      $previous_year = $current_year;
    }

    // Add the very last event which was left unclosed because we never get back in the cycle
    $events[$previous_unit][$last_start_day->format('Ynd')] = array($last_start_day, new \DateTime($previous_year . "-" . $previous_month . "-" . $days_in_month), $current_state);

    // With the day events taken care off let's cycle through hours
    while( $data = $results[BAT_HOUR]->fetchAssoc()) {
      dpm($data);
      unset($data['unit_id']);
      unset($data['year']);
      unset($data['month']);
      unset($data['day']);
      dpm(array_flip($data));

    }

    return $events;
  }

  /**
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param $store
   * @return array
   */
  public function getEventDates2(\DateTime $start_date, \DateTime $end_date, $store) {

    $queries  = $this->buildQueries($start_date, $end_date, $store);
    $results = $this->getEventData($queries);

    $events = array();

    // Cycle through day results and setup an event array
    while( $data = $results[BAT_DAY]->fetchAssoc()) {

      // Get the key columns and leave the day data on its own
      $unit_id = $data['unit_id'];
      unset($data['unit_id']);

      $year = $data['year'];
      unset($data['year']);

      $month = $data['month'];
      unset($data['month']);

      // Figure out how many days the current month has
      $temp_date = new \DateTime($year . "-" . $month);
      $days_in_month = (int)$temp_date->format('t');

      $flipped = array();
      $e = 0;
      $j = 0;
      $old_value = 0;

      // Cycle through day data and create events
      foreach ($data as $day => $value) {
        $j++;
        if ($j <= $days_in_month) {
          // If the value has changed and we are not just starting
          if (($value != $old_value)) {
            $e++;
            $flipped[$e][$value][$day] = $day;
            $old_value = $value;
          }
          else {
            $flipped[$e][$value][$day] = $day;
          }
        }
      }

      $events[BAT_DAY][$unit_id][$year][$month] = $flipped;
    }

    $dated_events = array();

    // Turn the array of split events into a set of continuous events
    foreach ($events[BAT_DAY] as $unit => $dates) {
      foreach ($dates as $year => $months) {
        foreach ($months as $month => $event_data) {
          foreach ($event_data as $event_id => $event) {
            foreach ($event as $value => $days) {
              dpm($days, 'days');
              // Get the last day
              $last_day = array_pop($days);
              // Flip the array around and pop again to get the first day
              $days = array_reverse($days);
              $first_day = array_pop($days);

              $last_day = new \DateTime($year . "-" . $month . "-" . substr($last_day,1));
              dpm($last_day);
              if ($first_day == ''){
                dpm('same_day_event');
                $first_day = $last_day;
              } else {
                $first_day = new \DateTime($year . "-" . $month . "-" . substr($first_day,1));
              }
              $dated_events[BAT_DAY][$unit][] = array($first_day, $last_day, $value);
            }
          }
        }
      }
    }

    dpm($dated_events);



    // With the day events taken care off let's cycle through hours
    while( $data = $results[BAT_HOUR]->fetchAssoc()) {
      dpm($data, 'HOURS');
      unset($data['unit_id']);
      unset($data['year']);
      unset($data['month']);
      unset($data['day']);
      dpm(array_flip($data));

    }

    return $events;
  }


  public function getEventData($queries) {
    $results = array();
    // Run each query and store results
    foreach ($queries as $type => $query) {
      $results[$type] = db_query($query);
    }

    return $results;
  }

  public function buildQueries($start_date, $end_date, $store) {
    $queries = array();

    $queries[BAT_DAY] = 'SELECT * FROM ' . $store[BAT_DAY] . ' WHERE ';
    $queries[BAT_HOUR] = 'SELECT * FROM ' . $store[BAT_HOUR] . ' WHERE ';
    $queries[BAT_MINUTE] = 'SELECT * FROM ' . $store[BAT_MINUTE] . ' WHERE ';

    $hours_query = TRUE;
    $minutes_query = TRUE;

    // Create a mock event which we will use to determine how to query the database
    $mock_event = new BatGranularEvent($start_date, $end_date, 0, -10);
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

    dpm($flipped, 'fllipped');

  }


  /**
   * {@inheritdoc}
   */
  public function monthDefined($event) {
    $month = $event->startMonth();
    $year = $event->startYear();
    $unit_id = $event->unit_id;

    $query = db_select($this->base_table, 'a');
    $query->addField('a', 'unit_id');
    $query->addField('a', 'year');
    $query->addField('a', 'month');
    $query->condition('a.unit_id', $unit_id);
    $query->condition('a.year', $year);
    $query->condition('a.month', $month);
    $result = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    if (count($result) > 0) {
      return TRUE;
    }
    return FALSE;
  }

}
