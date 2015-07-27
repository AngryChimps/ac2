Feature: location-delete
  In order to remove my location from the system
  As a user
  I need to be able to mark the location as deleted

  Scenario: Successfully delete a location object
    Given I have an authenticated user
    And The authenticated user has a company
    And I have a valid new location array
    And I create a test location for my test company
    When I delete the test location
    Then I get a status code "200"
    And Finally, I clean up my objects

  Scenario: Fail to delete a non-existent location
    Given I have an authenticated user
    When I delete a non-existent location
    Then I get a status code "404"
    And I get back a valid json object
    And The response contains a field named "error.code"
    And The value of the "error.code" field returned is of type "string"
    And The value of the "error.code" field is "Api.LocationController.indexDeleteAction.1"
    And Finally, I clean up my objects

  Scenario: Fail to delete a location owned by someone else
    Given I have an authenticated user
    And I have a test company
    And The test company has a test location
    And I change the test companys "administerMemberIds" property to an empty array
    And I save changes to the test company
    When I delete the test location
    Then I get a status code "401"
    And I get back a valid json object
    And The response contains a field named "error.code"
    And The value of the "error.code" field returned is of type "string"
    And The value of the "error.code" field is "Api.LocationController.indexDeleteAction.3"
    And Finally, I clean up my objects

