<?php
/**
 * @file EventTest.php
 */

namespace Roomify\bat;

class EventTest extends \PHPUnit_Framework_TestCase {

  public function __construct() {
    // Set default timezone.
    date_default_timezone_set('UTC');
  }

  /**
   * @covers \Roomify\bat\Event::__construct
   * @uses   \Roomify\bat\Event
   */
  public function testObjectCanBeConstructedFromStartAndEndDate() {
      $startDate = new \DateTime('2010-01-01');
      $endDate   = new \DateTime('2010-01-02');
      $e         = new Event($startDate, $endDate);
      $this->assertInstanceOf(Event::class, $e);
      return $e;
  }

}
