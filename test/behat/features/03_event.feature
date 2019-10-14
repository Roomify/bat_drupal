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

@api @javascript
Scenario: Create new availability event states
  Given I am logged in as a user with the "administer state entities" permission
  When I am on "admin/bat/events/state/add"
  Then I fill in "Name" with "Available"
  And I fill in "Color" with "#2bf72e"
  And I fill in "Calendar label" with "AV"
  And I fill in "Event type" with "Availability (availability)"
  Then I press the "Save" button
  And I fill in "machine_name" with "available"
  Then I press the "Save" button
  When I am on "admin/bat/events/state/add"
  Then I fill in "Name" with "Not Available"
  And I fill in "Color" with "#f33939"
  And I fill in "Calendar label" with "N/A"
  And I fill in "Event type" with "Availability (availability)"
  Then I press the "Save" button
  And I fill in "machine_name" with "not_available"
  Then I press the "Save" button

@api @javascript
Scenario: Create a new availability event
  Given I am logged in as a user with the "create bat_event entities" permission
  When I am on "admin/bat/events/event/add/availability"
  Then I fill in "Unit" with "Single 1 (1)"
  And I fill in "State" with "Available (1)"
  And I fill in "event_dates[0][value][date]" with "10-01-2020"
  And I fill in "event_dates[0][end_value][date]" with "10-15-2020"
  Then I press the "Save" button

@api @javascript
Scenario: Anonymous user cannot view Events
  Given I am an anonymous user
  When I am on "admin/bat/events/event/1"
  Then I should see text matching "Access denied"

@api @javascript
Scenario: User with permission "view own event of any type" cannot view Events of other users
  Given I am logged in as a user with the "view own bat_event entities" permission
  When I am on "admin/bat/events/event/1"
  Then I should see text matching "Access denied"

@api @javascript
Scenario: User with permission "view any event of any type" can view Events of other users
  Given I am logged in as a user with the "view any bat_event entity" permission
  When I am on "admin/bat/events/event/1"
  Then I should not see text matching "Access denied"
