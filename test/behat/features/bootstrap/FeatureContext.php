<?php

use Drupal\DrupalExtension\Context\DrupalSubContextBase,
    Drupal\Component\Utility\Random;

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;

use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Behat\Behat\Hook\Scope\BeforeScenarioScope,
    Behat\Behat\Hook\Scope\AfterScenarioScope;

use Behat\Behat\Context\CustomSnippetAcceptingContext;

use Drupal\DrupalDriverManager;


/**
 * Features context.
 */
class FeatureContext extends DrupalSubContextBase implements CustomSnippetAcceptingContext {

  /**
   * The Mink context
   *
   * @var Drupal\DrupalExtension\Context\MinkContext
   */
  private $minkContext;

  /**
   * Keep track of units so they can be cleaned up.
   *
   * @var array
   */
  public $units = array();

  /**
   * Keep track of Types so they can be cleaned up.
   *
   * @var array
   */
  public $Types = array();

  /**
   * Keep track of events so they can be cleaned up.
   *
   * @var array
   */
  public $events = array();

  /**
   * Keep track of event types so they can be cleaned up.
   *
   * @var array
   */
  public $eventTypes = array();

  /**
   * Keep track of created content types so they can be cleaned up.
   *
   * @var array
   */
  public $content_types = array();

  /**
   * Keep track of created fields so they can be cleaned up.
   *
   * @var array
   */
  public $fields = array();

  /**
   * Initializes context.
   * Every scenario gets its own context object.
   *
   * @param \Drupal\DrupalDriverManager $drupal
   *   The Drupal driver manager.
   */
  public function __construct(DrupalDriverManager $drupal) {
    parent::__construct($drupal);
  }

  public static function getAcceptedSnippetType() { return 'regex'; }

  /**
   * @BeforeScenario
   */
  public function before(BeforeScenarioScope $scope) {
    $environment = $scope->getEnvironment();
    $this->minkContext = $environment->getContext('Drupal\DrupalExtension\Context\MinkContext');
  }

  /**
   * @AfterScenario
   */
  public function after(AfterScenarioScope $scope) {
    foreach ($this->users as $user) {
      $query2 = new EntityFieldQuery();
      $query2->entityCondition('entity_type', 'bat_event')
        ->propertyCondition('uid', $user->uid);
      $result = $query2->execute();
      if (isset($result['bat_event'])) {
        $event_ids = array_keys($result['bat_event']);
        bat_event_delete_multiple($booking_ids);
      }
    }

    if (!empty($this->units)) {
      foreach ($this->units as $unit) {
        $unit->delete();
      }
    }

    if (!empty($this->Types)) {
      foreach ($this->Types as $type) {
        $type->delete();
      }
    }

    if (!empty($this->eventTypes)) {
      foreach ($this->eventTypes as $event_type) {
        $event_type->delete();
      }
    }

    if (!empty($this->events)) {
      bat_event_delete_multiple($this->events);
    }

    foreach ($this->content_types as $content_type) {
      node_type_delete($content_type);
    }

    foreach ($this->fields as $field) {
      field_delete_field($field);
    }

  }

  /**
   * @When /^I am on the "([^"]*)" type$/
   */
  public function iAmOnTheType($type_name) {
    $this->iAmDoingOnTheType('view', $type_name);
  }

  /**
   * @When /^I am editing the "([^"]*)" type$/
   */
  public function iAmEditingTheType($type_name) {
    $this->iAmDoingOnTheType('edit', $type_name);
  }


  /**
   * Asserts that a given node type is editable.
   */
  public function assertEditNodeOfType($type) {
    $node = (object) array('type' => $type);
    $saved = $this->getDriver()->createNode($node);
    $this->nodes[] = $saved;

    // Set internal browser on the node edit page.
    $this->getSession()->visit($this->locatePath('/node/' . $saved->nid . '/edit'));
  }

  /**
   * Fill commerce address form fields in a single step.
   */
  private function fillCommerceAddress($args, $type) {
    // Replace <random> or member <property> token if is set for any field
    foreach ($args as $delta => $arg) {
      if (preg_match("/^<random>$/", $arg, $matches)) {
        $random = new Random();
        $args[$delta] = $random->name();
      }
    }

    // Need to manually fill country to trigger the AJAX refresh of fields for given country
    $country_field = str_replace('\\"', '"', "{$type}[commerce_customer_address][und][0][country]");
    $country_value = str_replace('\\"', '"', $args[4]);
    $this->getSession()->getPage()->fillField($country_field, $country_value);
    $this->minkContext->iWaitForAjaxToFinish();

    $this->minkContext->fillField("{$type}[commerce_customer_address][und][0][locality]", $args[1]);
    $this->minkContext->fillField("{$type}[commerce_customer_address][und][0][administrative_area]", $args[2]);
    $this->minkContext->fillField("{$type}[commerce_customer_address][und][0][postal_code]", $args[3]);
    $this->minkContext->fillField("{$type}[commerce_customer_address][und][0][thoroughfare]", $args[0]);
  }

  /**
   * Retrieves the last booking ID.
   *
   * @return int
   *   The last booking ID.
   *
   * @throws RuntimeException
   */
  protected function getLastBooking() {
    $efq = new EntityFieldQuery();
    $efq->entityCondition('entity_type', 'rooms_booking')
      ->entityOrderBy('entity_id', 'DESC')
      ->range(0, 1);
    $result = $efq->execute();
    if (isset($result['rooms_booking'])) {
      $return = key($result['rooms_booking']);
      return $return;
    }
    else {
      throw new RuntimeException('Unable to find the last booking');
    }
  }

  /**
   * Checks if one unit is being locked by a booking in a date range.
   * @param $unit_name
   * @param $start_date
   * @param $end_date
   * @param $status
   */
  protected function checkUnitLockedByLastBooking($unit_name, $start_date, $end_date, $status) {
    $booking_id = $this->getLastBooking();
    $expected_value = rooms_availability_assign_id($booking_id, $status);
    $this->checkUnitPropertyRange($unit_name, $start_date, $end_date, $expected_value, 'availability');
  }

  /**
   * Adds options field to any room_unit or room_unit_type entity.
   *
   * @param TableNode $table
   *   Table containing options definitions.
   * @param $wrapper
   *   The entity wrapper to attach the options.
   */
  protected function addOptionsToEntity(TableNode $table, $wrapper) {
    $delta = 0;
    if (isset($wrapper->rooms_booking_unit_options)) {
      $delta = count($wrapper->rooms_booking_unit_options);
    }

    foreach ($table->getHash() as $entityHash) {
      $wrapper->rooms_booking_unit_options[$delta] = $entityHash;
      $delta++;
    }
    $wrapper->save();
  }

  /**
   * Fills the constraint range field form.
   *
   * @param $minimum
   * @param $maximum
   * @param $constraint_type
   * @param $start_day
   * @param $start
   * @param $end
   */
  protected function addAvailabilityConstraint($minimum = NULL, $maximum = NULL, $constraint_type = NULL, $start_day = NULL, $start = NULL, $end = NULL) {
    $items = $this->getSession()->getPage()->findAll('css', 'table[id^="rooms-constraints-range-values"] tbody tr');
    $delta = count($items) - 1;

    if ($constraint_type == 'must') {
      $this->minkContext->pressButton('add_checkin_day_' . $delta);
    }
    else {
      $this->minkContext->pressButton('add_min_max_' . $delta);
    }
    $this->minkContext->iWaitForAjaxToFinish();

    if (!isset($start) || !isset($end)) {
      $this->minkContext->selectOption('rooms_constraints_range[und][' . $delta . '][group_conditions][period]', 'always');
    }
    else {
      $this->minkContext->selectOption('rooms_constraints_range[und][' . $delta . '][group_conditions][period]', 'dates');
      $this->minkContext->iWaitForAjaxToFinish();

      $start_date = new DateTime($start);
      $end_date = new DateTime($end);
      $this->minkContext->fillField('rooms_constraints_range[und][' . $delta . '][group_conditions][start_date][date]', $start_date->format('d/m/Y'));
      $this->minkContext->fillField('rooms_constraints_range[und][' . $delta . '][group_conditions][end_date][date]', $end_date->format('d/m/Y'));
    }

    if (isset($start_day)) {
      if ($constraint_type == 'must') {
        $this->minkContext->selectOption('rooms_constraints_range[und][' . $delta . '][group_conditions][booking_must_start]', $start_day);
      }
      elseif ($constraint_type == 'if') {
        $this->minkContext->checkOption('rooms_constraints_range[und][' . $delta . '][group_conditions][booking_if_start]');
        $this->minkContext->iWaitForAjaxToFinish();

        $this->minkContext->selectOption('rooms_constraints_range[und][' . $delta . '][group_conditions][booking_if_start_day]', $start_day);
      }
    }

    if ($constraint_type != 'must') {
      if (is_numeric($minimum)) {
        $this->minkContext->checkOption('rooms_constraints_range[und][' . $delta . '][group_conditions][minimum_stay]');
        $this->minkContext->iWaitForAjaxToFinish();

        $this->minkContext->fillField('rooms_constraints_range[und][' . $delta . '][group_conditions][minimum_stay_nights]', $minimum);
      }

      if (is_numeric($maximum)) {
        $this->minkContext->checkOption('rooms_constraints_range[und][' . $delta . '][group_conditions][maximum_stay]');
        $this->minkContext->iWaitForAjaxToFinish();

        $this->minkContext->fillField('rooms_constraints_range[und][' . $delta . '][group_conditions][maximum_stay_nights]', $maximum);
      }
    }

    $this->minkContext->pressButton('rooms_constraints_range_add_more');
    $this->minkContext->iWaitForAjaxToFinish();
  }

  /**
   * @param $unit_name
   * @param $start
   * @param $end
   * @return bool
   */
  protected function findUnitAvailability($unit_name, $start, $end) {
    $unit_id = $this->findBookableUnitByName($unit_name);
    $start_date = new DateTime($start);
    $end_date = new DateTime($end);

    $agent = new AvailabilityAgent($start_date, $end_date);
    $units = $agent->checkAvailability();

    if (is_array($units)) {
      foreach ($units as $units_per_type) {
        foreach ($units_per_type as $units) {
          foreach ($units as $id => $unit) {
            if ($id == $unit_id) {
              return TRUE;
            }
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * Fills a field using JS to avoid event firing.
   * @param string $field
   * @param string$value
   *
   */
  protected function fillFieldByJS($field, $value) {
    $field = str_replace('\\"', '"', $field);
    $value = str_replace('\\"', '"', $value);
    $xpath = $this->getSession()->getPage()->findField($field)->getXpath();

    $element = $this->getSession()->getDriver()->getWebDriverSession()->element('xpath', $xpath);
    $elementID = $element->getID();
    $subscript = "arguments[0]";
    $script = str_replace('{{ELEMENT}}', $subscript, '{{ELEMENT}}.value = "' . $value . '"');
    return $this->getSession()->getDriver()->getWebDriverSession()->execute(array(
      'script' => $script,
      'args' => array(array('ELEMENT' => $elementID))
    ));
  }

}
