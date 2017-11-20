Feature: Once bat_event is installed
  In order to create Events
  As a site administrator
  I should be able to access the event type page

@api @javascript
Scenario: Type manager can access the Event Types page and create, edit and delete Event types
  Given I am logged in as a user with the "administer bat_event_type entities,administer bat_event fields" permission
  When I am on "admin/bat/events/event/event-types"
  Then I should see the link "Add event type"
  When I click "Add event type"
  Then I am on "admin/bat/events/event/event-types/add"
  When I fill in "Label" with "Availability"
  Then I wait 3 seconds
  When I check the box fixed_event_states
  When I press the "Save event type" button
  Then I should be on "admin/bat/events/event/event-types"
  And I should see text matching "Availability"
  When I click "Manage fields"
  Then I should be on "admin/bat/events/event/event-types/availability/edit/fields"
  When I click "Add field"
  Then the url should match "admin/bat/events/event/event-types/availability/edit/fields/add-field"
  When I select "text" from "new_storage_type"
  When I fill in "Label" with "Custom Label"
  Then I wait 3 seconds
  When I press the "Save and continue" button
  Then I wait 3 seconds
  Then the url should match "admin/bat/events/event/event-types/availability/edit/fields/bat_event.availability.field_custom_label/storage"
  When I press the "Save field settings" button
  Then the url should match "admin/bat/events/event/event-types/availability/edit/fields/bat_event.availability.field_custom_label"
  When I press the "Save settings" button
  Then the url should match "admin/bat/events/event/event-types/availability/edit/fields"
  When I click "Edit"
  When I select "field_custom_label" from "event_label[default_event_label_field_name]"
  When I press the "Save event type" button
  Then I should be on "admin/bat/events/event/event-types"
  And I should see text matching "Availability"
