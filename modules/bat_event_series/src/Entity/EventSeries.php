<?php

/**
 * @file
 * Contains \Drupal\bat_event_series\Entity\EventSeries.
 */

namespace Drupal\bat_event_series\Entity;

use Roomify\Bat\Calendar\Calendar;
use Roomify\Bat\Store\DrupalDBStore;
use Roomify\Bat\Unit\Unit;

use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\bat_event_series\EventSeriesInterface;
use Drupal\user\UserInterface;
use Drupal\user\EntityOwnerTrait;
use RRule\RRule;

/**
 * Defines the Event Series entity.
 *
 * @ingroup bat
 *
 * @ContentEntityType(
 *   id = "bat_event_series",
 *   label = @Translation("Event series"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\bat_event_series\EventSeriesListBuilder",
 *     "views_data" = "Drupal\bat_event_series\Entity\EventSeriesViewsData",
 *     "form" = {
 *       "default" = "Drupal\bat_event_series\Entity\Form\EventSeriesForm",
 *       "add" = "Drupal\bat_event_series\Entity\Form\EventSeriesForm",
 *       "edit" = "Drupal\bat_event_series\Entity\Form\EventSeriesForm",
 *       "delete" = "Drupal\bat_event_series\Entity\Form\EventSeriesDeleteForm",
 *       "delete_events" = "Drupal\bat_event_series\Entity\Form\EventSeriesDeleteEventsForm",
 *     },
 *     "access" = "Drupal\bat_event_series\EventSeriesAccessControlHandler",
 *   },
 *   base_table = "event_series",
 *   admin_permission = "administer event_series entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *     "owner" = "uid",
 *   },
 *   bundle_entity_type = "bat_event_series_type",
 *   field_ui_base_route = "entity.bat_event_series_type.edit_form",
 *   permission_granularity = "bundle",
 *   links = {
 *     "canonical" = "/admin/event_series/{bat_event_series}",
 *     "edit-form" = "/admin/event_series/{bat_event_series}/edit",
 *     "delete-form" = "/admin/event_series/{bat_event_series}/delete"
 *   }
 * )
 */
class EventSeries extends ContentEntityBase implements EventSeriesInterface {
  use EntityChangedTrait, EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'uid' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRRule() {
    return $this->get('rrule')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    $is_new = $this->isNew();

    parent::save();

    if ($is_new) {
      $event_series_type = bat_event_series_type_load($this->bundle());

      $event_granularity = $event_series_type->getEventGranularity();

      $event_type = bat_event_type_load($event_series_type->getTargetEventType());

      $field_name = 'event_' . $event_series_type->getTargetEntityType() . '_reference';

      $start = new \DateTime($this->get('event_dates')->value);
      $end = new \DateTime($this->get('event_dates')->end_value);

      $rrule = new RRule($this->getRRule(), $start);

      foreach ($rrule as $occurrence) {
        $event = bat_event_create([
          'type' => $event_type->id(),
        ]);

        $start_date = clone($occurrence);
        $end_date = clone($occurrence);

        if ($event_granularity == 'bat_daily') {
          $end_date->add($start->diff($end));

          $start_date->setTime(0, 0);
          $end_date->setTime(0, 0);

          $event_dates = [
            'value' => $start_date->format('Y-m-d\T00:00:00'),
            'end_value' => $end_date->format('Y-m-d\T00:00:00'),
          ];
        }
        else {
          $start_date->setTime($start->format('H'), $start->format('i'));
          $end_date->setTime($start->format('H'), $start->format('i'));

          $end_date->add($start->diff($end));

          $event_dates = [
            'value' => $start_date->format('Y-m-d\TH:i:00'),
            'end_value' => $end_date->format('Y-m-d\TH:i:00'),
          ];
        }

        $unit = $this->get($field_name)->entity;

        if ($this->checkAvailability($start_date, $end_date, $event_type, $unit)) {
          $event->set('event_dates', $event_dates);
          $event->set('event_state_reference', $this->get('event_state_reference')->entity->id());
          $event->set($field_name, $unit->id());
          $event->set('event_series', $this->id());
          $event->save();
        }
        else {
          if ($event_granularity == 'bat_daily') {
            \Drupal::messenger()->addWarning(t('Unable to create event from @start to @end, availability was not found.', [
              '@start' => $start_date->format('M j Y'),
              '@end' => $end_date->format('M j Y'),
            ]));
          }
          else {
            \Drupal::messenger()->addWarning(t('Unable to create event from @time on @date, availability was not found.', [
              '@time' => $start_date->format('gA') . '-' . $end_date->format('gA'),
              '@date' => $start_date->format('M j Y'),
            ]));
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Event Series entity.'))
      ->setReadOnly(TRUE);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setDescription(t('The name of the Event Series entity.'))
      ->setSettings([
        'max_length' => 200,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['rrule'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('RRule'))
      ->setDisplayOptions('form', [
        'type' => 'text_textfield',
        'weight' => 0,
      ])
      ->setRequired(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Event entity.'))
      ->setReadOnly(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Event entity.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback(static::class . '::getCurrentUserId')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The event series type.'))
      ->setSetting('target_type', 'bat_event_series_type');

    return $fields;
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

  /**
   * @param $start_date
   * @param $end_date
   * @param $event_type
   * @param $unit
   * @return bool
   */
  private function checkAvailability($start_date, $end_date, $event_type, $unit) {
    $target_field_name = 'event_' . $event_type->getTargetEntityType() . '_reference';

    $database = Database::getConnectionInfo('default');

    $prefix = (isset($database['default']['prefix']['default'])) ? $database['default']['prefix']['default'] : '';

    $event_store = new DrupalDBStore($event_type->id(), DrupalDBStore::BAT_EVENT, $prefix);

    $temp_end_date = clone($end_date);
    $temp_end_date->sub(new \DateInterval('PT1M'));

    $bat_units = [
      new Unit($unit->id(), 0),
    ];

    $calendar = new Calendar($bat_units, $event_store);

    $events = $calendar->getEvents($start_date, $temp_end_date);
    foreach ($events[$unit->id()] as $event) {
      $event_id = $event->getValue();

      if ($event = bat_event_load($event_id)) {
        $state = $event->get('event_state_reference')->entity;

        if ($state->getBlocking()) {
          return FALSE;
        }
      }
    }

    return TRUE;
  }

}
