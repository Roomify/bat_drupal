<?php

namespace Drupal\bat_unit\Form;

use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for content type deletion.
 */
class TypeBundleDeleteConfirm extends EntityDeleteForm {
}
