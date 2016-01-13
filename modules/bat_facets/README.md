This module provides a availability state facet for facetapi.

To use

* configure an index of Types
* add a facet on the Type id
* configure the facet to use the "Bat State" widget
* select the Event states to return when searching
* add the facet block to your search page/view (enable the search_api_views module if creating a view)
* the search will be filtered to types that have any units in the state being searched for (over the requested dates)
