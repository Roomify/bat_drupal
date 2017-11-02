Feature: Once bat_unit is installed
  In order to create Types
  As a site administrator
  I should be able to access the unit type page

@api @javascript
Scenario: Type manager can access the Types page and create, edit and delete Unit types
  Given I am logged in as a user with the "administer bat_type_bundle entities" permission
  When I am on "admin/bat/config/type-bundles"
  Then I should see the link "Add type bundle"
  When I click "Add type bundle"
  Then I am on "admin/bat/config/type-bundles/add"
  When I fill in "label" with "Room"
  When I press the "Save Bat Type bundle" button
  Then I should be on "admin/bat/config/type-bundles"
  And I should see the text "Room"
  And I should see the text "(Machine name: room)"
  Then I am on "admin/bat/config/type-bundles/manage/room"
  And the "label" field should contain "Room"
  Then I fill in "label" with "Hotel Room"
  When I press the "Save Bat Type bundle" button
  Then I should be on "admin/bat/config/type-bundles"
  When I am on "admin/bat/config/type-bundles/manage/room"
  And the "label" field should contain "Hotel Room"
  When I am on "admin/bat/config/type-bundles/manage/room/delete"
  And I press the "Confirm" button
  Then I should see the text "Deleted Type Bundle Hotel Room."

@api @javascript
Scenario: Create Type bundle, Type and Units
  Given I am logged in as a user with the "administer bat_type_bundle entities,bypass bat_type entities access,bypass bat_unit entities access,update any bat_type entity,create bat_unit entities" permissions
  When I am on "admin/bat/config/type-bundles"
  Then I should see the link "Add type bundle"
  When I click "Add type bundle"
  Then I am on "admin/bat/config/type-bundles/add"
  When I fill in "label" with "Room"
  When I press the "Save Bat Type bundle" button
  Then I should be on "admin/bat/config/type-bundles"
  And I should see the text "Room"
  And I should see the text "(Machine name: room)"
  Then I am on "admin/bat/config/type-bundles/manage/room"
  And the "label" field should contain "Room"
  Then I am on "admin/bat/config/types"
  Then I should see the link "Add a Type"
  When I click "Add a Type"
  Then I am on "admin/bat/config/types/add/room"
  When I fill in "name" with "Single"
  When I press the "Save Type" button
  Then I should be on "admin/bat/config/types"
  And I should see the text "Single"
  Then I am on "admin/bat/unit-management"
  When I click "Units"
  Then the url should match "admin/bat/config/types/manage/\d/units"
  Then I should see the link "Add Units"
  When I click "Add Units"
  And I should see the text "Number of Units to create"
  When I fill in "units" with "5"
  When I press the "Add" button
  Then the url should match "admin/bat/config/types/manage/\d/units"
  And I should see the text "Single 1"
