<?php

/**
 * @file
 * Contains \Drupal\bat_event\Entity\Event.
 */

namespace Drupal\bat_event\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Event entities.
 */
class EventViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['event']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Event'),
      'help' => $this->t('The Event ID.'),
    );

    $data['event']['start_date'] = array(
      'title' => t('Start Date'),
      'help' => t("A event's start date."),
      'field' => array(
        'float' => TRUE,
        'id' => 'bat_event_handler_date_field',
        'click sortable' => TRUE,
      ),
      'filter' => array(
        'id' => 'bat_event_handler_date_filter',
      ),
    );
    $data['event']['end_date'] = array(
      'title' => t('End Date'),
      'help' => t("A event's end date."),
      'field' => array(
        'float' => TRUE,
        'id' => 'bat_event_handler_date_field',
        'click sortable' => TRUE,
      ),
      'filter' => array(
        'id' => 'bat_event_handler_date_filter',
      ),
    );
    $data['event']['type']['field'] = array(
      'title' => t('Event Type'),
      'help' => t('The event type label.'),
      'id' => 'bat_event_handler_event_type_field',
    );

    // Expose the uid as a relationship to users.
    $data['event']['uid'] = array(
      'title' => t('Uid'),
      'help' => t("The owner's user ID."),
      'field' => array(
        'id' => 'views_handler_field_user',
        'click sortable' => TRUE,
      ),
      'argument' => array(
        'id' => 'views_handler_argument_user_uid',
        'name field' => 'name',
      ),
      'filter' => array(
        'title' => t('Name'),
        'id' => 'views_handler_filter_user_name',
      ),
      'sort' => array(
        'id' => 'views_handler_sort',
      ),
      'relationship' => array(
        'title' => t('Owner'),
        'help' => t("Relate this event to its owner's user account"),
        'id' => 'views_handler_relationship',
        'base' => 'users',
        'base field' => 'uid',
        'field' => 'uid',
        'label' => t('Event owner'),
      ),
    );

    $data['event']['duration'] = array(
      'title' => t('Duration'),
      'help' => t("Event's duration."),
      'field' => array(
        'id' => 'bat_event_handler_duration_field',
        'click sortable' => TRUE,
      ),
    );

    $data['event']['event_value'] = array(
      'title' => t('Value'),
      'help' => t("Event's value."),
      'field' => array(
        'id' => 'bat_event_handler_value_field',
      ),
    );

    $data['event']['blocking'] = array(
      'title' => t('Blocking'),
      'help' => t("Event's blocking state."),
      'filter' => array(
        'id' => 'bat_event_handler_blocking_filter',
      ),
    );

    return $data;
  }

}
