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

    $data['event']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('Event'),
      'help' => $this->t('The Event ID.'),
    ];

    $data['event']['start_date'] = [
      'title' => $this->t('Start Date'),
      'help' => $this->t("A event's start date."),
      'field' => [
        'float' => TRUE,
        'id' => 'bat_event_handler_date_field',
        'click sortable' => TRUE,
      ],
      'filter' => [
        'id' => 'bat_event_handler_date_filter',
      ],
    ];
    $data['event']['end_date'] = [
      'title' => $this->t('End Date'),
      'help' => $this->t("A event's end date."),
      'field' => [
        'float' => TRUE,
        'id' => 'bat_event_handler_date_field',
        'click sortable' => TRUE,
      ],
      'filter' => [
        'id' => 'bat_event_handler_date_filter',
      ],
    ];
    $data['event']['type']['field'] = [
      'title' => $this->t('Event Type'),
      'help' => $this->t('The event type label.'),
      'id' => 'bat_event_handler_event_type_field',
    ];

    // Expose the uid as a relationship to users.
    $data['event']['uid'] = [
      'title' => $this->t('Uid'),
      'help' => $this->t("The owner's user ID."),
      'field' => [
        'id' => 'views_handler_field_user',
        'click sortable' => TRUE,
      ],
      'argument' => [
        'id' => 'views_handler_argument_user_uid',
        'name field' => 'name',
      ],
      'filter' => [
        'title' => $this->t('Name'),
        'id' => 'views_handler_filter_user_name',
      ],
      'sort' => [
        'id' => 'views_handler_sort',
      ],
      'relationship' => [
        'title' => $this->t('Owner'),
        'help' => $this->t("Relate this event to its owner's user account"),
        'id' => 'views_handler_relationship',
        'base' => 'users',
        'base field' => 'uid',
        'field' => 'uid',
        'label' => $this->t('Event owner'),
      ],
    ];

    $data['event']['duration'] = [
      'field' => [
        'title' => $this->t('Duration'),
        'help' => $this->t("Event's duration."),
        'id' => 'bat_event_handler_duration_field',
      ],
    ];

    $data['event']['event_value'] = [
      'title' => $this->t('Value'),
      'help' => $this->t("Event's value."),
      'field' => [
        'id' => 'bat_event_handler_value_field',
      ],
    ];

    $data['event']['blocking'] = [
      'title' => $this->t('Blocking'),
      'help' => $this->t("Event's blocking state."),
      'filter' => [
        'id' => 'bat_event_handler_blocking_filter',
      ],
    ];

    $data['event']['start_fulldate'] = [
      'title' => $this->t('Start date'),
      'help' => $this->t('Date in the form of CCYYMMDD.'),
      'argument' => [
        'field' => 'start',
        'id' => 'date_fulldate',
      ],
    ];
    $data['event']['start_year_month'] = [
      'title' => $this->t('Start year + month'),
      'help' => $this->t('Date in the form of YYYYMM.'),
      'argument' => [
        'field' => 'start',
        'id' => 'date_year_month',
      ],
    ];
    $data['event']['start_year'] = [
      'title' => $this->t('Start year'),
      'help' => $this->t('Date in the form of YYYY.'),
      'argument' => [
        'field' => 'start',
        'id' => 'date_year',
      ],
    ];
    $data['event']['start_month'] = [
      'title' => $this->t('Start month'),
      'help' => $this->t('Date in the form of MM (01 - 12).'),
      'argument' => [
        'field' => 'start',
        'id' => 'date_month',
      ],
    ];
    $data['event']['start_day'] = [
      'title' => $this->t('Start day'),
      'help' => $this->t('Date in the form of DD (01 - 31).'),
      'argument' => [
        'field' => 'start',
        'id' => 'date_day',
      ],
    ];
    $data['event']['start_week'] = [
      'title' => $this->t('Start week'),
      'help' => $this->t('Date in the form of WW (01 - 53).'),
      'argument' => [
        'field' => 'start',
        'id' => 'date_week',
      ],
    ];

    $data['event']['end_fulldate'] = [
      'title' => $this->t('End date'),
      'help' => $this->t('Date in the form of CCYYMMDD.'),
      'argument' => [
        'field' => 'end',
        'id' => 'date_fulldate',
      ],
    ];
    $data['event']['end_year_month'] = [
      'title' => $this->t('End year + month'),
      'help' => $this->t('Date in the form of YYYYMM.'),
      'argument' => [
        'field' => 'end',
        'id' => 'date_year_month',
      ],
    ];
    $data['event']['end_year'] = [
      'title' => $this->t('End year'),
      'help' => $this->t('Date in the form of YYYY.'),
      'argument' => [
        'field' => 'end',
        'id' => 'date_year',
      ],
    ];
    $data['event']['end_month'] = [
      'title' => $this->t('End month'),
      'help' => $this->t('Date in the form of MM (01 - 12).'),
      'argument' => [
        'field' => 'end',
        'id' => 'date_month',
      ],
    ];
    $data['event']['end_day'] = [
      'title' => $this->t('End day'),
      'help' => $this->t('Date in the form of DD (01 - 31).'),
      'argument' => [
        'field' => 'end',
        'id' => 'date_day',
      ],
    ];
    $data['event']['end_week'] = [
      'title' => $this->t('End week'),
      'help' => $this->t('Date in the form of WW (01 - 53).'),
      'argument' => [
        'field' => 'end',
        'id' => 'date_week',
      ],
    ];

    return $data;
  }

}
