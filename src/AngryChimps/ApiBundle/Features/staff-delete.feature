Feature: staff-delete
  In order to remove a staff object from the system
  As a user
  I need to be able to mark the staff object as deleted

  Scenario: Successfully delete a staff object
    Given I get a new session
    And I create a new member
    And I create a new company
    And I create a new location
    And I create a new staff member
    When I send a "delete" message to the "staff" api with id from the "staff.id" variable
    Then I get a status code "200"
    And I get back a valid json object
    And Finally, I clean up my objects
