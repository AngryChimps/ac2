Feature: staff-patch
  In order to change staff information
  As a user
  I need to be able to patch a staff object

  Scenario: Successfully modify a staff object
    Given I get a new session
    And I create a new member
    And I create a new company
    And I create a new location
    And I create a new staff member
    When I get the "staff" data for id "staff.id"
    And I have a sample request array for the "staff" api, "patch" method
    And I change the request array "payload.first" field to "buddy"
    And I send a "patch" message to the "staff" api with id from the "staff.id" variable
    Then I get a status code "200"
    And I get back a valid json object
    And Finally, I clean up my objects
