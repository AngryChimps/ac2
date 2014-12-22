Feature: location-put
  In order to change my location information
  As a user
  I need to be able to modify and save a location object

  Scenario: Successfully modify a location object
    Given I have an authenticated user
    And The authenticated user has a company
    And I create a test company
    And I have a valid new location array
    And I create a test location for my test company
    And I change the test locations "name" property to "234 Maple Lane"
    When I put changes to the test location
    Then I get a status code "200"
    And I get back a valid json object
    And If I reload the test location
    And The value of the "name" field of the test location is "234 Maple Lane"
    And Finally, I clean up my objects

  Scenario: Attempt to modify a location with an invalid id
    Given I have an authenticated user
    And The authenticated user has a company
    And I create a test company
    And I have a valid new location array
    And I create a test location for my test company
    And I change the test locations "name" property to "314 Broadway Avenue"
    When I put changes to the test location with the wrong id
    Then I get a status code "404"
    And I get back a valid json object
    And The response contains a field named "error.code"
    And The value of the "error.code" field returned is of type "string"
    And The value of the "error.code" field is "Api.LocationController.indexPutAction.1"
    And Finally, I clean up my objects

    # This is impossible to happen in a valid environment
#  Scenario: Attempt to modify a location with an invalid company_id
#    Given I have an authenticated user
#    And The authenticated user has a company
#    And I create a test company
#    And I have a valid new location array
#    And I create a test location for my test company
#    And I change the test locations "companyId" property to "b"
#    When I put changes to the test location
#    Then I get a status code "400"
#    And I get back a valid json object
#    And The response contains a field named "error.code"
#    And The value of the "error.code" field returned is of type "string"
#    And The value of the "error.code" field is "Api.LocationController.indexPutAction.2"
#    And Finally, I clean up my objects

  Scenario: Attempt to modify a location the authenticated user does not own
    Given Another user has a company
    And The test company has a test location
    And I have an authenticated user
    And I change the test locations "name" property to "a"
    When I put changes to the test location
    Then I get a status code "401"
    And I get back a valid json object
    And The response contains a field named "error.code"
    And The value of the "error.code" field returned is of type "string"
    And The value of the "error.code" field is "Api.LocationController.indexPutAction.3"
    And Finally, I clean up my objects

  Scenario: Attempt to modify a location object with invalid data
    Given I have an authenticated user
    And The authenticated user has a company
    And I create a test company
    And I have a valid new location array
    And I create a test location for my test company
    And I change the test locations "name" property to "a"
    When I put changes to the test location
    Then I get a status code "400"
    And I get back a valid json object
    And The response contains a field named "error.code"
    And The value of the "error.code" field returned is of type "string"
    And The value of the "error.code" field is "Api.LocationController.indexPutAction.4"
    And Finally, I clean up my objects
