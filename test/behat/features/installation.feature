Feature: Have Bat correctly installed
  In order to use Bat
  As a site administrator
  I need to be able to access all the Bat configuration screens

@api
Scenario: I can access the Types interface
  Given I am logged in as a user with the "administrator" role
  When I am on "admin/bat"
  Then I should see the link "Unit Management"
  And I should not see the text "Unitss"
