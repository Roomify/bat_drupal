<?php


class UnitMonthlyPricingCalendar extends UnitPricingCalendar {

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
    if (isset($unit_type->data['pricing_monthly_field'])) {
      $field_price = $unit_type->data['pricing_monthly_field'];
      if (isset($this->unit->{$field_price}[LANGUAGE_NONE][0]['amount'])) {
        $this->default_price = $this->unit->{$field_price}[LANGUAGE_NONE][0]['amount'] / 100;
      }
    }

    $this->price_modifiers = $price_modifiers;

    $this->base_table = 'bat_monthly_pricing';
  }

  public function calculatePrice(DateTime $start_date, DateTime $end_date, $persons = 0, $children = 0, $children_ages = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function getEvents(DateTime $start_date, DateTime $end_date) {
    // Get the raw day results.
    $results = $this->getRawDayData($start_date, $end_date);
    $events = array();

    foreach ($results[$this->unit_id] as $year => $months) {
      foreach ($months['states'] as $state) {
        // Create a booking event.
        $start = $state['start_month'];
        $end = $state['end_month'];

        $sd = new DateTime("$year-$start-01");
        $ed = clone($sd);
        $ed->modify('+' . ($end - $start + 1) . ' months - 1 day');

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
  public function getRawDayData(DateTime $start_date, DateTime $end_date) {
    // Create a dummy PricingEvent to represent the range we are searching over.
    // This gives us access to handy functions that PricingEvents have.
    $s = new PricingEvent($this->unit_id, 0, $start_date, $end_date);

    $results = array();

    // If search across the same year do a single query.
    if ($s->sameYear()) {
      $query = db_select('bat_monthly_pricing', 'a');
      $query->fields('a');
      $query->condition('a.unit_id', $this->unit_id);
      $query->condition('a.year', $s->startYear());
      $years = $query->execute()->fetchAll(PDO::FETCH_ASSOC);
      if (count($years) > 0) {
        foreach ($years as $year) {
          $y = $year['year'];
          $id = $year['unit_id'];
          // Remove the three first rows and just keep the weeks.
          unset($year['year']);
          unset($year['unit_id']);
          $results[$id][$y]['months'] = $year;
        }
      }
    }
    // For multiple years do a query for each year.
    else {
      for ($j = $s->startYear(); $j <= $s->endYear(); $j++) {
        $query = db_select('bat_monthly_pricing', 'a');
        $query->fields('a');
        $query->condition('a.unit_id', $this->unit_id);
        $query->condition('a.year', $j);
        $years = $query->execute()->fetchAll(PDO::FETCH_ASSOC);
        if (count($years) > 0) {
          foreach ($years as $year) {
            $y = $year['year'];
            $id = $year['unit_id'];
            unset($year['year']);
            unset($year['unit_id']);
            $results[$id][$y]['months'] = $year;
          }
        }
      }
    }

    // With the results from the db in place fill in any missing months
    // with the default state for the unit.
    for ($j = $s->startYear(); $j <= $s->endYear(); $j++) {
      if (!isset($results[$this->unit_id][$j])) {
        $results[$this->unit_id][$j]['months'] = array();
        for ($m = 1; $m <= 12; $m++) {
          $results[$this->unit_id][$j]['months']['m' . $m] = '-1';
        }
      }
    }

    // With all the months in place we now need to clean results to set the
    // right start and end date for each month - this will save code downstream
    // from having to worry about it.
    foreach ($results[$this->unit_id] as $year => $months) {
      if ($year == $s->startYear()) {
        $mid = $s->startMonth();

        for ($i = 1; $i < $mid; $i++) {
          unset($results[$this->unit_id][$year]['months']['m' . $i]);
        }
      }
      if ($year == $s->endYear()) {
        $mid = $s->endMonth();

        for ($i = $mid + 1; $i <= 12; $i++) {
          unset($results[$this->unit_id][$year]['months']['m' . $i]);
        }
      }
    }

    // We store -1 instead of the default price in the DB so this is our chance to get the default price back
    // cycling through the data and replace -1 with the current default price of the unit.
    foreach ($results[$this->unit_id] as $year => $weeks) {
      foreach ($weeks['months'] as $month => $price) {
        if ($results[$this->unit_id][$year]['months'][$month] == '-1') {
          $results[$this->unit_id][$year]['months'][$month] = commerce_currency_decimal_to_amount($this->default_price, commerce_default_currency());
        }
      }
    }

    // With the results in place we do a states array with the start and
    // end months of each event.
    foreach ($results[$this->unit_id] as $year => $months) {
      reset($months['months']);

      $j = 1;
      $i = substr(key($months['months']), 1);

      $start_month = $i;
      $end_month = NULL;
      $unique_states = array();
      $old_state = $months['months']['m' . $i];
      $state = $months['months']['m' . $i];
      while ($j <= count($months['months'])) {
        $state = $months['months']['m' . $i];
        if ($state != $old_state) {
          $unique_states[] = array(
            'state' => $old_state,
            'start_month' => $start_month,
            'end_month' => $i - 1,
          );
          $end_month = $i - 1;
          $start_month = $i;
          $old_state = $state;
        }
        $i++;
        $j++;
      }
      // Get the last event in.
      $unique_states[] = array(
        'state' => $state,
        'start_month' => isset($end_month) ? $end_month + 1 : $start_month,
        'end_month' => $i - 1,
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
          $this->addMonthlyEvent($event);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareFullMonthArray(BatEventInterface $event) {
    $months = array();

    for ($i = 1; $i <= 12; $i++) {
      if (($i >= $event->startMonth()) && ($i <= $event->endMonth())) {
        $months['m' . $i] = commerce_currency_decimal_to_amount($event->amount, commerce_default_currency());
      }
      else {
        // When we are writing a new month to the DB make sure to have the placeholder value -1 for the days where the
        // default price is in effect. This means as a user changes the default price we will take it into account even
        // though the price data is now in a DB row.
        $months['m' . $i] = -1;
      }
    }
    return $months;
  }

  /**
   * {@inheritdoc}
   */
  protected function preparePartialMonthArray(BatEventInterface $event) {
    $months = array();
    for ($i = $event->startMonth(); $i <= $event->endMonth(); $i++) {
      $months['m' . $i] = commerce_currency_decimal_to_amount($event->amount, commerce_default_currency());
    }
    return $months;
  }

  /**
   * {@inheritdoc}
   */
  public function calculatePricingEvents($unit_id, $amount, DateTime $start_date, DateTime $end_date, $operation, $days) {
    $events = array();

    $start = new DateTime($start_date->format('Y-m') . '-01');

    do {
      $end = clone($start);
      $end->modify('+ 1 months - 1 day');

      $events[] = new PricingEvent($unit_id, $amount, clone($start), clone($end), $operation, $days);

      $start->modify('+ 1 month');

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
    $result = $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    if (count($result) > 0) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function addMonthlyEvent(BatEventInterface $event) {
    // First check if the month exists and do an update if so
    if ($this->YearDefined($event->startYear())) {
      $partial_month_row = $this->preparePartialMonthArray($event);
      $update = db_update($this->base_table)
        ->condition('unit_id', $this->unit_id)
        ->condition('year', $event->startYear())
        ->fields($partial_month_row)
        ->execute();
    }
    // Do an insert for a new month
    else {
      // Prepare the days array
      $days = $this->prepareFullMonthArray($event);
      $month_row = array(
        'unit_id' => $this->unit_id,
        'year' => $event->startYear(),
      );
      $month_row = array_merge($month_row, $days);
      $insert = db_insert($this->base_table)->fields($month_row);
      $insert->execute();
    }
  }
}
