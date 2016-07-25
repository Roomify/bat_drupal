Feature: Once bat_event is installed
  In order to create Events
  As a site administrator
  I should be able to access the event type page

@api @javascript
Scenario: Type manager can access the Event Types page and create, edit and delete Event types
  Given I am logged in as a user with the "administer bat_event_type entities,administer fields" permission
  When I am on "admin/bat/events/event-types"
  Then I should see the link "Add event type"
  When I click "Add event type"
  Then I am on "admin/bat/events/event-types/add"
  When I fill in "label" with "Availability"
  When I check the box fixed_event_states
  When I press the "Save Event type" button
  Then I should be on "admin/bat/events/event-types"
  And I should see text matching "Availability"
  And I should see the text "(Machine name: availability)"
  When I click "manage fields"
  Then I should be on "admin/bat/events/event-types/manage/availability/fields"
  When I fill in "fields[_add_new_field][label]" with "Custom Label"
  When I select "Text" from "fields[_add_new_field][type]"
  When I press the "Save" button
  Then the url should match "admin/bat/events/event-types/manage/availability/fields/field_.*$"
  When I press the "Save field settings" button
  Then the url should match "admin/bat/events/event-types/manage/availability/fields/field_.*$"
  When I press the "Save settings" button
  Then the url should match "admin/bat/events/event-types/manage/availability/fields"
  When I click "Edit"
  When I select "field_custom_label" from "event_label[default_event_label_field_name]"
  When I press the "Save Event type" button
  Then I should be on "admin/bat/events/event-types"
  And I should see text matching "Availability"
  And I should see the text "(Machine name: availability)"
