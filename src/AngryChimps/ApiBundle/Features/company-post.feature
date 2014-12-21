Feature: company-post
  In order to create a company
  As a user
  I need to be able to post a new company object

  Scenario: Successfully create a company object
    Given I have an authenticated user
    And I have a valid new company array
    When I create a test company
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.company.id"
    And The value of the "payload.company.id" field returned is of type "string"
    And The string length of the "payload.company.id" field is "32"
    And Finally, I clean up my objects

  Scenario: Attempt to create a company object with invalid data
    Given I have an authenticated user
    And I have a valid new company array
    And I change the "payload.name" field's value of the request object to "a"
    When I create a test company
    Then I get a status code "400"
    And I get back a valid json object
    And The response contains a field named "error.code"
    And The value of the "error.code" field returned is of type "string"
    And The value of the "error.code" field is "Api.CompanyController.indexPostAction.1"
    And Finally, I clean up my objects
