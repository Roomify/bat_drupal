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
  When I fill in "Name" with "Room"
  Then I wait 3 seconds
  When I press the "Save type bundle" button
  Then I should be on "admin/bat/config/type-bundles"
  And I should see the text "Room"
  Then I am on "admin/bat/config/type-bundles/room/edit"
  And the "Name" field should contain "Room"
  Then I fill in "Name" with "Hotel Room"
  When I press the "Save type bundle" button
  Then I should be on "admin/bat/config/type-bundles"
  When I am on "admin/bat/config/type-bundles/room/edit"
  And the "Name" field should contain "Hotel Room"
  When I am on "admin/bat/config/type-bundles/room/delete"
  And I press the "Delete" button
  Then I should see the text "The type bundle Hotel Room has been deleted."

@api @javascript
Scenario: Create Type bundle, Type and Units
  Given I am logged in as a user with the "administer bat_type_bundle entities,bypass bat_unit_type entities access,bypass bat_unit entities access,update any bat_unit_type entity,create bat_unit entities,view any bat_unit_type entity,view any bat_unit entity" permissions
  When I am on "admin/bat/config/type-bundles"
  Then I should see the link "Add type bundle"
  When I click "Add type bundle"
  Then I am on "admin/bat/config/type-bundles/add"
  When I fill in "Name" with "Room"
  Then I wait 3 seconds
  When I press the "Save type bundle" button
  Then I should be on "admin/bat/config/type-bundles"
  And I should see the text "Room"
  Then I am on "admin/bat/config/type-bundles/room/edit"
  And the "Name" field should contain "Room"
  Then I am on "admin/bat/config/unit_type"
  Then I should see the link "Add Type"
  When I click "Add Type"
  Then I am on "admin/bat/config/unit_type/add/room"
  When I fill in "Name" with "Single"
  When I press the "Save and publish" button
  Then the url should match "admin/bat/config/unit_type/\d+/edit"
  And I should see the text "Single"
  Then I am on "admin/bat/unit-management"
  When I click "Units"
  Then the url should match "admin/bat/config/types/type/\d+/units"
  Then I should see the link "Add Units"
  When I click "Add Units"
  When I fill in "Name" with "Single 1"
  And I fill in "Unit Type" with "Single"
  Then I press the "Save and publish" button
  When I am on "admin/bat/config/unit"
  Then I should see the text "Single 1"
