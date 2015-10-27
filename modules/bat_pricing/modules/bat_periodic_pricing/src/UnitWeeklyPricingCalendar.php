<?php

namespace Drupal\bat_periodic_pricing;

use Drupal\bat\BatEventInterface;
use Drupal\bat_pricing\PricingEvent;
use Drupal\bat_pricing\UnitPricingCalendar;

class UnitWeeklyPricingCalendar extends UnitPricingCalendar {

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
    if (isset($unit_type->data['pricing_weekly_field'])) {
      $field_price = $unit_type->data['pricing_weekly_field'];
      if (isset($this->unit->{$field_price}[LANGUAGE_NONE][0]['amount'])) {
        $this->default_price = $this->unit->{$field_price}[LANGUAGE_NONE][0]['amount'] / 100;
      }
    }

    $this->price_modifiers = $price_modifiers;

    $this->base_table = 'bat_weekly_pricing';
  }

  public function calculatePrice(\DateTime $start_date, \DateTime $end_date, $persons = 0, $children = 0, $children_ages = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function getEvents(\DateTime $start_date, \DateTime $end_date) {
    // Get the raw day results.
    $results = $this->getRawDayData($start_date, $end_date);
    $events = array();

    foreach ($results[$this->unit_id] as $year => $weeks) {
      foreach ($weeks['states'] as $state) {
        // Create a booking event.
        $start = $state['start_week'];
        $end = $state['end_week'];

        $sd = new \DateTime();
        $sd->setISODate($year, $start, intval(variable_get('date_first_day', 0)));
        $ed = new \DateTime();
        $ed->setISODate($year, $end, intval(variable_get('date_first_day', 0)));
        $ed->modify('+6 days');

        $amount = commerce_currency_amount_to_decimal($state['state'], commerce_default_currency());

        $event = new PricingEvent($this->unit_id, $amount, $sd, $ed);
        $events[] = $event;
      }
    }

    return $events;
  }

  /**
   * {@inheritdoc}
   */
  public function getRawDayData(\DateTime $start_date, \DateTime $end_date) {
    // Create a dummy PricingEvent to represent the range we are searching over.
    // This gives us access to handy functions that PricingEvents have.
    $s = new PricingEvent($this->unit_id, 0, $start_date, $end_date);

    $start_year = $s->startYear();
    if ($s->startWeek() == 1 && $s->startMonth() == 12) {
      $start_year += 1;
    }

    $end_year = $s->endYear();
    if ($s->endWeek() == 53 && $s->endMonth() == 1) {
      $end_year -= 1;
    }

    $results = array();

    // If search across the same year do a single query.
    if ($s->sameYear()) {
      $query = db_select('bat_weekly_pricing', 'a');
      $query->fields('a');
      $query->condition('a.unit_id', $this->unit_id);
      $query->condition('a.year', $start_year);
      $years = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
      if (count($years) > 0) {
        foreach ($years as $year) {
          $y = $year['year'];
          $id = $year['unit_id'];
          // Remove the three first rows and just keep the weeks.
          unset($year['year']);
          unset($year['unit_id']);
          $results[$id][$y]['weeks'] = $year;
        }
      }
    }
    // For multiple years do a query for each year.
    else {
      for ($j = $start_year; $j <= $end_year; $j++) {
        $query = db_select('bat_weekly_pricing', 'a');
        $query->fields('a');
        $query->condition('a.unit_id', $this->unit_id);
        $query->condition('a.year', $j);
        $years = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
        if (count($years) > 0) {
          foreach ($years as $year) {
            $y = $year['year'];
            $id = $year['unit_id'];
            unset($year['year']);
            unset($year['unit_id']);
            $results[$id][$y]['weeks'] = $year;
          }
        }
      }
    }

    // With the results from the db in place fill in any missing months
    // with the default state for the unit.
    for ($j = $start_year; $j <= $end_year; $j++) {
      if (!isset($results[$this->unit_id][$j])) {
        $results[$this->unit_id][$j]['weeks'] = array();
        for ($w = 1; $w <= 53; $w++) {
          $results[$this->unit_id][$j]['weeks']['w' . $w] = '-1';
        }
      }
    }

    // With all the months in place we now need to clean results to set the
    // right start and end date for each month - this will save code downstream
    // from having to worry about it.
    foreach ($results[$this->unit_id] as $year => $wekks) {
      if ($year == $start_year) {
        $mid = $s->startWeek();

        for ($i = 1; $i < $mid; $i++) {
          unset($results[$this->unit_id][$year]['weeks']['w' . $i]);
        }
      }
      if ($year == $end_year) {
        $mid = $s->endWeek();

        for ($i = $mid + 1; $i <= 53; $i++) {
          unset($results[$this->unit_id][$year]['weeks']['w' . $i]);
        }
      }
    }

    // We store -1 instead of the default price in the DB so this is our chance to get the default price back
    // cycling through the data and replace -1 with the current default price of the unit.
    foreach ($results[$this->unit_id] as $year => $weeks) {
      foreach ($weeks['weeks'] as $week => $price) {
        if ($results[$this->unit_id][$year]['weeks'][$week] == '-1') {
          $results[$this->unit_id][$year]['weeks'][$week] = commerce_currency_decimal_to_amount($this->default_price, commerce_default_currency());
        }
      }
    }

    // Remove week 53 for years with 52 weeks.
    foreach ($results[$this->unit_id] as $year => $weeks) {
      if (bat_periodic_pricing_get_iso_weeks_in_year($year) == 52) {
        unset($results[$this->unit_id][$year]['weeks']['w53']);
      }
    }

    // With the results in place we do a states array with the start and
    // end months of each event.
    foreach ($results[$this->unit_id] as $year => $weeks) {
      reset($weeks['weeks']);

      $j = 1;
      $i = substr(key($weeks['weeks']), 1);

      $start_week = $i;
      $end_week = NULL;
      $unique_states = array();
      $old_state = $weeks['weeks']['w' . $i];
      $state = $weeks['weeks']['w' . $i];
      while ($j <= count($weeks['weeks'])) {
        $state = $weeks['weeks']['w' . $i];
        if ($state != $old_state) {
          $unique_states[] = array(
            'state' => $old_state,
            'start_week' => $start_week,
            'end_week' => $i - 1,
          );
          $end_week = $i - 1;
          $start_week = $i;
          $old_state = $state;
        }
        $i++;
        $j++;
      }
      // Get the last event in.
      $unique_states[] = array(
        'state' => $state,
        'start_week' => isset($end_week) ? $end_week + 1 : $start_week,
        'end_week' => $i - 1,
      );
      $results[$this->unit_id][$year]['states'] = $unique_states;
    }

    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function updateCalendar($events) {

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

          $monthly_events[] = $a_event;
        }

        foreach ($monthly_events as $event) {
          if (intval(variable_get('date_first_day', 0)) == 0) {
            $event->start_date->modify('+1 day');
            $event->end_date->modify('+1 day');
          }

          $this->addWeeklyEvent($event);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareFullMonthArray(BatEventInterface $event) {
    $weeks = array();

    for ($i = 1; $i <= 53; $i++) {
      if (($i >= $event->startWeek()) && ($i <= $event->endWeek())) {
        $weeks['w' . $i] = commerce_currency_decimal_to_amount($event->amount, commerce_default_currency());
      }
      else {
        // When we are writing a new month to the DB make sure to have the placeholder value -1 for the days where the
        // default price is in effect. This means as a user changes the default price we will take it into account even
        // though the price data is now in a DB row.
        $weeks['w' . $i] = -1;
      }
    }
    return $weeks;
  }

  /**
   * {@inheritdoc}
   */
  protected function preparePartialMonthArray(BatEventInterface $event) {
    $weeks = array();
    for ($i = intval($event->startWeek()); $i <= intval($event->endWeek()); $i++) {
      $weeks['w' . $i] = commerce_currency_decimal_to_amount($event->amount, commerce_default_currency());
    }
    return $weeks;
  }

  /**
   * {@inheritdoc}
   */
  public function calculatePricingEvents($unit_id, $amount, \DateTime $start_date, \DateTime $end_date, $operation, $days) {
    $events = array();

    $start = new \DateTime();
    if ($start_date->format('m') == 12 && $start_date->format('W') == 1) {
      $start->setISODate($start_date->format('Y') + 1, $start_date->format('W'));
    }
    else {
      $start->setISODate($start_date->format('Y'), $start_date->format('W'));
    }

    do {
      $end = clone($start);
      $end->modify('+ 1 week - 1 day');

      $events[] = new PricingEvent($unit_id, $amount, clone($start), clone($end), $operation, $days);

      $start->modify('+ 1 week');

    } while ($start <= $end_date);

    return $events;
  }

  /**
   * {@inheritdoc}
   */
  public function YearDefined($year) {
    $query = db_select($this->base_table, 'a');
    $query->addField('a', 'unit_id');
    $query->addField('a', 'year');
    $query->condition('a.unit_id', $this->unit_id);
    $query->condition('a.year', $year);
    $result = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    if (count($result) > 0) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function addWeeklyEvent(BatEventInterface $event) {
    $start_year = $event->startYear();
    if ($event->startWeek() == 1 && $event->startMonth() == 12) {
      $start_year += 1;
    }

    // First check if the month exists and do an update if so
    if ($this->YearDefined($start_year)) {
      $partial_month_row = $this->preparePartialMonthArray($event);
      $update = db_update($this->base_table)
        ->condition('unit_id', $this->unit_id)
        ->condition('year', $start_year)
        ->fields($partial_month_row)
        ->execute();
    }
    // Do an insert for a new month
    else {
      // Prepare the weeks array
      $weeks = $this->prepareFullMonthArray($event);
      $month_row = array(
        'unit_id' => $this->unit_id,
        'year' => $start_year,
      );
      $month_row = array_merge($month_row, $weeks);
      $insert = db_insert($this->base_table)->fields($month_row);
      $insert->execute();
    }
  }
}
