Feature: location-get
  In order to display information about a company
  As a user
  I need to be able to get a company object

  Scenario: Get a location with a valid id without authenticating
    Given Another user has a company
    And The test company has a test location
    And I have an authenticated user
    When I get the location data for the test location
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.location.id"
    And The value of the "payload.location.id" field returned is of type "string"
    And The string length of the "payload.location.id" field is "32"
    And Finally, I clean up my objects

#    This scenario may bring back private fields in the future to test
  Scenario: Get a location after authenticating
    Given I have an authenticated user
    And The authenticated user has a company
    And The test company has a test location
    When I get the location data for the test location
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.location.id"
    And The value of the "payload.location.id" field returned is of type "string"
    And The string length of the "payload.location.id" field is "32"
    And Finally, I clean up my objects

  Scenario: Fail to get a location with an invalid id
    Given I have an authenticated user
    When I get the location data for a fake location
    Then I get a status code "404"
    And I get back a valid json object
    And The response contains a field named "error.code"
    And The value of the "error.code" field returned is of type "string"
    And The value of the "error.code" field is "Api.LocationController.indexGetAction.1"
    And Finally, I clean up my objects
