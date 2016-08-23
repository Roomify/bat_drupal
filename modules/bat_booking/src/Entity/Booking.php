<?php

/**
 * @file
 * Contains \Drupal\bat_booking\Entity\Booking.
 */

namespace Drupal\bat_booking\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Booking entity.
 *
 * @ingroup bat
 *
 * @ContentEntityType(
 *   id = "bat_booking",
 *   label = @Translation("Booking"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\bat_booking\BookingListBuilder",
 *     "views_data" = "Drupal\bat_booking\Entity\BookingViewsData",
 *     "form" = {
 *       "default" = "Drupal\bat_booking\Entity\Form\BookingForm",
 *       "add" = "Drupal\bat_booking\Entity\Form\BookingForm",
 *       "edit" = "Drupal\bat_booking\Entity\Form\BookingForm",
 *       "delete" = "Drupal\bat_booking\Entity\Form\BookingDeleteForm",
 *     },
 *     "access" = "Drupal\bat_booking\BookingAccessControlHandler",
 *   },
 *   base_table = "booking",
 *   admin_permission = "administer Booking entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *   },
 *   bundle_entity_type = "bat_booking_bundle",
 *   field_ui_base_route = "entity.bat_booking_bundle.edit_form",
 *   permission_granularity = "bundle",
 *   links = {
 *     "canonical" = "/admin/booking/{bat_booking}",
 *     "edit-form" = "/admin/booking/{bat_booking}/edit",
 *     "delete-form" = "/admin/booking/{bat_booking}/delete"
 *   }
 * )
 */
class Booking extends ContentEntityBase {
	use EntityChangedTrait;

	/**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
  	$fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Unit entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Unit entity.'))
      ->setReadOnly(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Unit entity.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the entity was created.'))
      ->setDisplayOptions('form', array(
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The unit bundle.'))
      ->setSetting('target_type', 'bat_booking_bundle');

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Published'))
      ->setDefaultValue(TRUE);

    return $fields;
  }

}
