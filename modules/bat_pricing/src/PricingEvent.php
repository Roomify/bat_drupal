<?php

/**
 * @file
 * contains PricingEvent.
 */

namespace Drupal\bat_pricing;

use Drupal\bat\BatEventInterface;
use Drupal\bat\BatEvent;

class PricingEvent extends BatEvent implements PricingEventInterface {

  /**
   * The amount for this period.
   *
   * @var int
   */
  public $amount;

  /**
   * The operation to perform.
   *
   * @var string
   */
  public $operation;

  /**
   * Constructs a BookingPrice item.
   *
   * @param int $unit_id
   *   The unit ID.
   * @param int $amount
   *   The booking amount.
   * @param \DateTime $start_date
   *   The start date of the event.
   * @param \DateTime $end_date
   *   The start date of the event.
   * @param string $operation
   *   The operation to perform.
   */
  public function __construct($unit_id, $amount, $start_date, $end_date, $operation = '') {
    $this->unit_id = $unit_id;
    $this->amount = $amount;
    $this->start_date = $start_date;
    $this->end_date = $end_date;
    $this->operation = $operation;
  }

  /**
   * {@inheritdoc}
   */
  protected function createEvent(\DateTime $start_date, \DateTime $end_date) {
    return new PricingEvent($this->unit_id, $this->amount, $start_date, $end_date, $this->operation);
  }

  /**
   * {@inheritdoc}
   */
  public function applyOperation($amount, $operation) {
    switch ($operation) {
      case BAT_REPLACE:
        $this->amount = $amount;
        break;

      case BAT_ADD:
        $this->amount = $this->amount + $amount;
        break;

      case BAT_SUB:
        $this->amount = $this->amount - $amount;
        break;

      case BAT_INCREASE:
        $this->amount = $this->amount + (($this->amount) * ($amount / 100));
        break;

      case BAT_DECREASE:
        $this->amount = $this->amount - (($this->amount) * ($amount / 100));
        break;

      default:
        break;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function formatJson() {
    $amount = $this->amount;

    $event = array(
      "id" => $amount,
      "start" => $this->startYear() . '-' . $this->startMonth('m') . '-' . $this->startDay('d') . 'T13:00:00Z',
      "end" => $this->endYear() . '-' . $this->endMonth('m') . '-' . $this->endDay('d') . 'T13:00:00Z',
    );

    // Set the color.
    if ($this->amount < 100) {
      $event['color']  = 'orange';
      $event['title'] = "$this->amount";
    }
    elseif ($this->amount >= 100) {
      $event['color'] = 'green';
      $event['title'] = "$amount";
    }

    return $event;
  }

}
