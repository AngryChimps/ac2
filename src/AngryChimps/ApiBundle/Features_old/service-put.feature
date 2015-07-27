Feature: service-put
  In order to modify a service
  As a user
  I need to be able to put new service information to the server

  Scenario: Put new service information successfully
    Given I have an authenticated user
    And I have a test company
    And The test company has a test location
    And The test location has a test service
    And I change the test service's "name" field to "Service Checkup"
    When I put changes to the service
    Then I get a status code "200"
    And I get back a valid json object
    And The value of the "name" field of the test service is "Service Checkup"
    And Finally, I clean up my objects
