Feature: service-post
  In order to create a new service
  As a user
  I need to be able to post the service information

  Scenario: Post a new service successfully
    Given I have an authenticated user
    And I have a test company
    And The test company has a test location
    And I have a valid service array
    When I post the service data array
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.service.id"
    And The value of the "payload.service.id" field returned is of type "string"
    And The string length of the "payload.service.id" field is "32"
    And Finally, I clean up my objects
