<?php

/**
 * @file
 * Contains UnitPricingCalendar.
 */

namespace Drupal\bat_pricing;

use Drupal\bat\BatCalendar;
use Drupal\bat\BatEventInterface;

/**
 * Handles querying and updating the pricing information
 * relative to a single bookable unit.
 */
class UnitPricingCalendar extends BatCalendar implements UnitPricingCalendarInterface {

  /**
   * The actual unit relevant to this Calendar.
   */
  protected $unit;

  /**
   * The default price for the unit
   *
   * @var float
   */
  protected $default_price;

  /**
   * Price modifiers - an array of operations to be performed to the price.
   * Operations are performed in the sequence they are found in the array
   *
   * @var array
   */
  protected $price_modifiers;

  /**
   * Constructs a UnitPricingCalendar instance.
   *
   * @param int $unit_id
   *   The unit ID.
   * @param array $price_modifiers
   *   The price modifiers to apply.
   */
  public function __construct($unit_id, $price_modifiers = array()) {
    $this->unit_id = $unit_id;
    // Load the booking unit.
    $this->unit = bat_unit_load($unit_id);
    $this->default_state = $this->unit->default_state;

    $unit_type = bat_unit_type_load($this->unit->type);
    if (isset($unit_type->data['pricing_field'])) {
      $field_price = $unit_type->data['pricing_field'];
      if (isset($this->unit->{$field_price}[LANGUAGE_NONE][0]['amount'])) {
        $this->default_price = $this->unit->{$field_price}[LANGUAGE_NONE][0]['amount'] / 100;
      }
    }

    $this->price_modifiers = $price_modifiers;

    $this->base_table = 'bat_pricing';
  }

  /**
   * {@inheritdoc}
   */
  public function calculatePrice(\DateTime $start_date, \DateTime $end_date, $persons = 0, $children = 0) {

    $price = 0;
    $booking_price = 0;
    $booking_days = 0;

    // Setup pricing reply and log
    $reply = array(
      'full_price' => $price,
      'booking_price' => $booking_price,
      'log' => '',
    );

    // Get settings to add to log
    $reply['log']['bat_price_calculation'] = variable_get('bat_price_calculation', BAT_PER_NIGHT);

    $pricing_events = $this->getEvents($start_date, $end_date);
    $reply['log']['pricing_events'] = $pricing_events;
    foreach ($pricing_events as $event) {
      $days = $event->diff()->days + 1;
      $booking_days += $days;
      if (variable_get('bat_price_calculation', BAT_PER_NIGHT) == BAT_PER_PERSON) {
        $price = $price + ($days * $event->amount * ($persons - $children));
      }
      else {
        $price = $price + ($days * $event->amount);
      }
    }

    $booking_info = array(
      'unit' => $this->unit,
      'start_date' => $start_date,
      'end_date' => $end_date,
      'booking_parameters' => array(
        'group_size' => $persons,
      ),
    );

    drupal_alter('bat_booking_amount_before_modifiers', $price, $booking_info);

    $price = $this->applyPriceModifiers($price, $booking_days, $reply);
    $payment_option = variable_get('bat_payment_options', FULL_PAYMENT);
    $reply['bat_payment_option'] = variable_get('bat_payment_options', FULL_PAYMENT);
    switch ($payment_option) {
      case FULL_PAYMENT:
        $booking_price = $price;
        break;

      case PERCENT_PAYMENT:
        $reply['bat_payment_option'][PERCENT_PAYMENT] = variable_get('bat_payment_options_percentual');
        $booking_price = $price / 100 * variable_get('bat_payment_options_percentual');
        break;

      case FIRST_NIGHT_PAYMENT:
        $booking_price = $pricing_events[0]->amount;
        $reply['bat_payment_option'][FIRST_NIGHT_PAYMENT] = $booking_price;
        break;
    }
    $reply['full_price'] = $price;
    $reply['booking_price'] = $booking_price;
    return $reply;
  }

  /**
   * {@inheritdoc}
   */
  public function applyPriceModifiers($base_price, $days, &$reply) {
    $price = $base_price;
    if (!empty($this->price_modifiers)) {
      foreach ($this->price_modifiers as $source => $modifier) {

        if ($modifier['#type'] == BAT_PRICE_SINGLE_OCCUPANCY) {
          $reply['log']['modifiers'][$source][$mod_count][BAT_PRICE_SINGLE_OCCUPANCY]['pre'] = $price;
          $reply['log']['modifiers'][$source][$mod_count][BAT_PRICE_SINGLE_OCCUPANCY]['amount'] = $this->unit->data['singlediscount'];
          $reply['log']['modifiers'][$source][$mod_count][BAT_PRICE_SINGLE_OCCUPANCY]['modifier'] = $modifier;
            $this->unit->data['singlediscount'];
          $price -= $base_price * $this->unit->data['singlediscount'] / 100;
          $reply['log']['modifiers'][$source][BAT_PRICE_SINGLE_OCCUPANCY]['post'] = $price;
        }
        elseif ($modifier['#type'] == BAT_DYNAMIC_MODIFIER) {
          $reply['log']['modifiers'][$source][BAT_DYNAMIC_MODIFIER]['modifier'] = $modifier;
          $reply['log']['modifiers'][$source][BAT_DYNAMIC_MODIFIER]['pre'] = $price;
          switch ($modifier['#op_type']) {
            case BAT_ADD:
              $price += $modifier['#amount'] * $modifier['#quantity'];
              $reply['log']['modifiers'][$source][BAT_DYNAMIC_MODIFIER]['op'] = BAT_ADD;
              $reply['log']['modifiers'][$source][BAT_DYNAMIC_MODIFIER]['post'] = $price;
              break;

            case BAT_ADD_DAILY:
              $price += $modifier['#amount'] * $modifier['#quantity'] * $days;
              $reply['log']['modifiers'][$source][BAT_DYNAMIC_MODIFIER]['op'] = BAT_ADD_DAILY;
              $reply['log']['modifiers'][$source][BAT_DYNAMIC_MODIFIER]['post'] = $price;
              break;

            case BAT_SUB:
              $price -= $modifier['#amount'] * $modifier['#quantity'];
              $reply['log']['modifiers'][$source][BAT_DYNAMIC_MODIFIER]['op'] = BAT_SUB;
              $reply['log']['modifiers'][$source][BAT_DYNAMIC_MODIFIER]['post'] = $price;
              break;

            case BAT_SUB_DAILY:
              $price -= $modifier['#amount'] * $modifier['#quantity'] * $days;
              $reply['log']['modifiers'][$source][BAT_DYNAMIC_MODIFIER]['op'] = BAT_SUB_DAILY;
              $reply['log']['modifiers'][$source][BAT_DYNAMIC_MODIFIER]['post'] = $price;
              break;

            case BAT_REPLACE:
              $price = $modifier['#amount'];
              $reply['log']['modifiers'][$source][BAT_DYNAMIC_MODIFIER]['op'] = BAT_REPLACE;
              $reply['log']['modifiers'][$source][BAT_DYNAMIC_MODIFIER]['post'] = $price;
              break;

            case BAT_INCREASE:
              $price += $base_price * ($modifier['#amount'] * $modifier['#quantity']) / 100;
              $reply['log']['modifiers'][$source][BAT_DYNAMIC_MODIFIER]['op'] = BAT_INCREASE;
              $reply['log']['modifiers'][$source][BAT_DYNAMIC_MODIFIER]['post'] = $price;
              break;

            case BAT_DECREASE:
              $price -= $base_price * ($modifier['#amount'] * $modifier['#quantity']) / 100;
              $reply['log']['modifiers'][$source][BAT_DYNAMIC_MODIFIER]['op'] = BAT_DECREASE;
              $reply['log']['modifiers'][$source][BAT_DYNAMIC_MODIFIER]['post'] = $price;
              break;
          }
        }
      }
    }
    return $price;
  }

  /**
   * {@inheritdoc}
   */
  public function getEvents(\DateTime $start_date, \DateTime $end_date) {
    // Get the raw day results.
    $results = $this->getRawDayData($start_date, $end_date);
    $events = array();
    foreach ($results[$this->unit_id] as $year => $months) {
      foreach ($months as $mid => $month) {
        // Event array gives us the start days for each event within a month.
        $start_days = array_keys($month['states']);
        foreach ($month['states'] as $state) {
          // Create a booking event.
          $start = $state['start_day'];
          $end = $state['end_day'];
          $sd = new \DateTime("$year-$mid-$start");
          $ed = new \DateTime("$year-$mid-$end");
          $amount = commerce_currency_amount_to_decimal($state['state'], commerce_default_currency());
          $event = new PricingEvent($this->unit_id, $amount, $sd, $ed);
          $events[] = $event;
        }
      }
    }
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  public function getRawDayData(\DateTime $start_date, \DateTime $end_date) {
    // To handle single-day bookings (Tours) we pretend that they are overnight
    // bookings.
    if ($end_date < $start_date) {
      $end_date->add(new \DateInterval('P1D'));
    }

    // Create a dummy PricingEvent to represent the range we are searching over.
    // This gives us access to handy functions that PricingEvents have.
    $s = new PricingEvent($this->unit_id, 0, $start_date, $end_date);

    $results = array();

    // If search across the same year do a single query.
    if ($s->sameYear()) {
      $query = db_select('bat_pricing', 'a');
      $query->fields('a');
      $query->condition('a.unit_id', $this->unit_id);
      $query->condition('a.year', $s->startYear());
      $query->condition('a.month', $s->startMonth(), '>=');
      $query->condition('a.month', $s->endMonth(), '<=');
      $months = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
      if (count($months) > 0) {
        foreach ($months as $month) {
          $m = $month['month'];
          $y = $month['year'];
          $id = $month['unit_id'];
          // Remove the three first rows and just keep the days.
          unset($month['month']);
          unset($month['year']);
          unset($month['unit_id']);
          $results[$id][$y][$m]['days'] = $month;
        }
      }
    }
    // For multiple years do a query for each year.
    else {
      for ($j = $s->startYear(); $j <= $s->endYear(); $j++) {
        $query = db_select('bat_pricing', 'a');
        $query->fields('a');
        $query->condition('a.unit_id', $this->unit_id);
        $query->condition('a.year', $j);
        if ($j == $s->startYear()) {
          $query->condition('a.month', $s->startMonth(), '>=');
        }
        elseif ($j == $s->endYear()) {
          $query->condition('a.month', $s->endMonth(), '<=');
        }
        $months = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
        if (count($months) > 0) {
          foreach ($months as $month) {
            $m = $month['month'];
            $y = $month['year'];
            $id = $month['unit_id'];
            unset($month['month']);
            unset($month['year']);
            unset($month['unit_id']);
            $results[$id][$y][$m]['days'] = $month;
          }
        }
      }
    }

    // With the results from the db in place fill in any missing months
    // with the default state for the unit.
    for ($j = $s->startYear(); $j <= $s->endYear(); $j++) {
      $eod = bat_end_of_month_dates($j);

      // We start by setting the expected start and end months for each year.
      if ($s->sameYear()) {
        $expected_months = $s->endMonth() - $s->startMonth() + 1;
        $sm = $s->startMonth();
        $em = $s->endMonth();
      }
      elseif ($j == $s->endYear()) {
        $expected_months = $s->endMonth();
        $sm = 1;
        $em = $s->endMonth();
      }
      elseif ($j == $s->startYear()) {
        $expected_months = 12 - $s->startMonth() + 1;
        $em = 12;
        $sm = $s->startMonth();
      }
      else {
        $expected_months = 12;
        $sm = 1;
        $em = 12;
      }

      // We check to see if the months we have already fit our expectations.
      $actual_months = isset($result[$this->unit_id][$j]) ? count($results[$id][$j]) : 0;
      if ($expected_months > $actual_months) {
        // We have missing months so lets go fill them.
        for ($i = $sm; $i <= $em; $i++) {
          if (!isset($results[$this->unit_id][$j][$i])) {
            $last_day = $eod[$i];
            $month = $this->prepareFullMonthArray(new PricingEvent($this->unit_id,
              $this->default_price,
              new \DateTime("$j-$i-1"),
              new \DateTime("$j-$i-$last_day")));
            // Add the month in its rightful position.
            $results[$this->unit_id][$j][$i]['days'] = $month;
            // And sort months.
            ksort($results[$this->unit_id][$j]);
          }
        }
      }
    }

    // With all the months in place we now need to clean results to set the
    // right start and end date for each month - this will save code downstream
    // from having to worry about it.
    foreach ($results[$this->unit_id] as $year => $months) {
      foreach ($months as $mid => $days) {
        // There is undoubtetly a smarter way to do this.
        if (count($days['days']) != $eod[$mid]) {
          switch ($eod[$mid]) {
            case 30:
              unset($results[$this->unit_id][$year][$mid]['days']['d31']);
              break;

            case 29:
              unset($results[$this->unit_id][$year][$mid]['days']['d31']);
              unset($results[$this->unit_id][$year][$mid]['days']['d30']);
              break;

            case 28:
              unset($results[$this->unit_id][$year][$mid]['days']['d31']);
              unset($results[$this->unit_id][$year][$mid]['days']['d30']);
              unset($results[$this->unit_id][$year][$mid]['days']['d29']);
              break;
          }
        }
        if (($year == $s->startYear()) && ($mid == $s->startMonth())) {
          // We know we have the entire months over the range so we just unset
          // all the dates from the start of the month to the actual start day.
          for ($i = 1; $i < $s->startDay(); $i++) {
            unset($results[$this->unit_id][$year][$mid]['days']['d' . $i]);
          }
        }
        if (($year == $s->endYear()) && ($mid == $s->endMonth())) {
          // And from the end of the month back to the actual end day.
          for ($i = $s->endDay() + 1; $i <= $eod[$mid]; $i++) {
            unset($results[$this->unit_id][$year][$mid]['days']['d' . $i]);
          }
        }

      }
    }

    // We store -1 instead of the default price in the DB so this is our chance to get the default price back
    // cycling through the data and replace -1 with the current default price of the unit.
    foreach ($results[$this->unit_id] as $year => $months) {
      foreach ($months as $mid => $days) {
        // The number of days in the month we are interested in eventing.
        $j = count($days);
        // The start date.
        $i = substr(key($days['days']), 1);
        while ($j <= count($days['days'])) {
          if ($days['days']['d' . $i] == -1) {
            $results[$this->unit_id][$year][$mid]['days']['d' . $i] = commerce_currency_decimal_to_amount($this->default_price, commerce_default_currency());
          }
          $i++;
          $j++;
        }
      }
    }

    // With the results in place we do a states array with the start and
    // end dates of each event.
    foreach ($results[$this->unit_id] as $year => $months) {
      foreach ($months as $mid => $days) {
        // The number of days in the month we are interested in eventing.
        $j = count($days);
        // The start date.
        $i = substr(key($days['days']), 1);
        $start_day = $i;
        $end_day = NULL;
        $unique_states = array();
        $old_state = $days['days']['d' . $i];
        $state = $days['days']['d' . $i];
        while ($j <= count($days['days'])) {
          $state = $days['days']['d' . $i];
          if ($state != $old_state) {
            $unique_states[] = array(
              'state' => $old_state,
              'start_day' => $start_day,
              'end_day' => $i - 1,
            );
            $end_day = $i - 1;
            $start_day = $i;
            $old_state = $state;
          }
          $i++;
          $j++;
        }
        // Get the last event in.
        $unique_states[] = array(
          'state' => $state,
          'start_day' => isset($end_day) ? $end_day + 1 : $start_day,
          'end_day' => $i - 1,
        );
        $results[$this->unit_id][$year][$mid]['states'] = $unique_states;
      }
    }

    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function updateCalendar($events, $events_to_remove = array()) {
    foreach ($events as $event) {
      // Make sure event refers to the unit for this calendar.
      if ($event->unit_id == $this->unit_id) {
        // Get all the pricing events that fit within this event.
        $affected_events = $this->getEvents($event->start_date, $event->end_date);
        $monthly_events = array();

        foreach ($affected_events as $a_event) {
          /** @var PricingEventInterface $a_event */
          // Apply the operation.
          $a_event->applyOperation($event->amount, $event->operation);
          // If the event is in the same month span just queue to be added.
          if ($a_event->sameMonth()) {
            $monthly_events[] = $a_event;
          }
          else {
            // Check if multi-year - if not just create monthly events.
            if ($a_event->sameYear()) {
              $monthly_events_tmp = $a_event->transformToMonthlyEvents();
              $monthly_events = array_merge($monthly_events, $monthly_events_tmp);
            }
            else {
              // Else transform to single years and then to monthly.
              $yearly_events = $a_event->transformToYearlyEvents();
              foreach ($yearly_events as $ye) {
                $monthly_events_tmp = $ye->transformToMonthlyEvents();
                $monthly_events = array_merge($monthly_events, $monthly_events_tmp);
              }
            }
          }
        }

        foreach ($monthly_events as $event) {
          $this->addMonthEvent($event);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareFullMonthArray(BatEventInterface $event) {
    $days = array();
    $eod = bat_end_of_month_dates($event->startYear());
    $last_day = $eod[$event->startMonth()];

    for ($i = 1; $i <= $last_day; $i++) {
      if (($i >= $event->startDay()) && ($i <= $event->endDay())) {
        $days['d' . $i] = commerce_currency_decimal_to_amount($event->amount, commerce_default_currency());
      }
      else {
        // When we are writing a new month to the DB make sure to have the placeholder value -1 for the days where the
        // default price is in effect. This means as a user changes the default price we will take it into account even
        // though the price data is now in a DB row.
        $days['d' . $i] = -1;
      }
    }
    return $days;
  }

  /**
   * {@inheritdoc}
   */
  protected function preparePartialMonthArray(BatEventInterface $event) {
    $days = array();
    for ($i = $event->startDay(); $i <= $event->endDay(); $i++) {
      $days['d' . $i] = commerce_currency_decimal_to_amount($event->amount, commerce_default_currency());
    }
    return $days;
  }

  /**
   * {@inheritdoc}
   */
  public function calculatePricingEvents($unit_id, $amount, \DateTime $start_date, \DateTime $end_date, $operation, $days) {
    $s_timestamp = $start_date->getTimestamp();
    $e_timestamp = $end_date->getTimestamp();

    $events = array();

    do {
      $s_date = getdate($s_timestamp);
      $wday_start = $s_date['wday'];

      if (in_array($wday_start + 1, $days)) {
        $events[] = new PricingEvent($unit_id, $amount, new \DateTime(date('Y-m-d', $s_timestamp)), new \DateTime(date('Y-m-d', $s_timestamp)), $operation, $days);
      }

      $s_timestamp = strtotime('+1 days', $s_timestamp);

    } while ($s_timestamp <= $e_timestamp);

    return $events;
  }

}
