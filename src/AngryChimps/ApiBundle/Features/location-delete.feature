Feature: location-delete
  In order to remove my location from the system
  As a user
  I need to be able to mark the location as deleted

  Scenario: Successfully delete a location object
    Given I get a new session
    And I create a new member
    And I create a new company
    And I create a new location
    When I send a "delete" message to the "location" api with id from the "location.id" variable
    Then I get a status code "200"
    And I get back a valid json object
    And Finally, I clean up my objects
