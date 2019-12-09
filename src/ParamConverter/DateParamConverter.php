<?php

/**
 * @file
 * Contains \Drupal\bat\ParamConverter\DateParamConverter.
 */

namespace Drupal\bat\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;
use Drupal\Component\Utility\Html;

/**
 *
 */
class DateParamConverter implements ParamConverterInterface {

  public function convert($value, $definition, $name, array $defaults) {
    $date_string = Html::escape($value);

    try {
      $date = new \DateTime($date_string);
    }
    catch (\Exception $e) {
      $date = 0;
    }

    return $date;
  }

  public function applies($definition, $name, Route $route) {
    return (!empty($definition['type']) && $definition['type'] == 'bat_date');
  }

}
