<?php

class BatStateSearchAPIQuery implements FacetapiQueryTypeInterface {
  /**
   * Implements FacetapiQueryTypeInterface::getType().
   */
  static public function getType() {
    return 'bat_state';
  }

  /**
   * Implements FacetapiQueryTypeInterface::execute().
   */
  public function execute($query) {
  }

  /**
   * Implements FacetapiQueryTypeInterface::build().
   *
   * Unlike normal facets, we provide a static list of options.
   */
  public function build() {
    $build = array();
    return $build;
  }

}
