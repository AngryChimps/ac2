Feature: location-post
  In order to create a location
  As a user
  I need to be able to post a new location object

  Scenario: Successfully create a location object
    Given I have an authenticated user
    And The authenticated user has a company
    And I have a valid new location array
    When I create a test location for my test company
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.location.id"
    And The value of the "payload.location.id" field returned is of type "string"
    And The string length of the "payload.location.id" field is "16"
    And Finally, I clean up my objects

  Scenario: Attempt to create a location object with invalid data
    Given I have an authenticated user
    And The authenticated user has a company
    And I have a valid new location array
    And I change the "name" field's value of the request object to "a"
    When I create a test location for my test company
    Then I get a status code "400"
    And I get back a valid json object
    And The response contains a field named "error.code"
    And The value of the "error.code" field returned is of type "string"
    And The value of the "error.code" field is "Api.LocationController.indexPostAction.1"
    And Finally, I clean up my objects
