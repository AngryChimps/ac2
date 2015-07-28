Feature: location-patch
  In order to change my location information
  As a user
  I need to be able to patch a location object

  Scenario: Successfully modify a location object
    Given I get a new session
    And I create a new member
    And I create a new company
    And I create a new location
    When I get the "location" data for id "location.id"
    And I have a sample request array for the "location" api, "patch" method
    And I change the request array "payload.name" field to "East Boondocks"
    And I send a "patch" message to the "location" api with id from the "location.id" variable
    Then I get a status code "200"
    And I get back a valid json object
    And Finally, I clean up my objects
