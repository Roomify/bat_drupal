<?php

namespace Drupal\bat_event\Form;

use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for event type deletion.
 */
class EventTypeDeleteConfirm extends EntityDeleteForm {

}
