# BAT Facet

This module allows you to incorporate availability search for Units managed by BAT into any a view using faceted search.

(Yup - that's right - this *is* awesome *and* a game-changer. We thought you'd like it too...)

We achieve such ludicrous levels of awesomeness by providing an availability state facet for FacetAPI. The end. :-)

# Usage

* configure an index of Types
* add a facet on the Type id
* configure the facet to use the "Bat State" widget
* select the Event states to return when searching
* add the facet block to your search page/view (enable the search_api_views module if creating a view)
* the search will be filtered to types that have any units in the state being searched for (over the requested dates)


