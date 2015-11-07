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
dsm($this->facet);
/*
    $this->adapter->addFacet($this->facet, $query);
    if ($active = $this->adapter->getActiveItems($this->facet)) {
      // Check the first value since only one is allowed.
      $filter = self::mapFacetItemToFilter(key($active), $this->facet);
      if ($filter) {
        $this->addFacetFilter($query, $this->facet['field'], $filter);
      }
    }
 */
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
