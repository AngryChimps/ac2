Feature: location-post
  In order to create a location
  As a user
  I need to be able to post a new location object

  Scenario: Successfully create a location object
    Given I get a new session
    And I create a new member
    And I create a new company
    When I create a new location
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.location.id"
    And The value of the "payload.location.id" field returned is of type "string"
    And The string length of the "payload.location.id" field is "40"
    And Finally, I clean up my objects
