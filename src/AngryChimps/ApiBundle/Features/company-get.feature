Feature: company-get
  In order to display information about a company
  As a user
  I need to be able to get a company object

  Scenario: Get a company with a valid id without authenticating
    Given I have an authenticated user
    And Another user has a company
    When I get the company data for the company
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.company.id"
    And The value of the "payload.company.id" field returned is of type "string"
    And The string length of the "payload.company.id" field is "16"
    And Finally, I clean up my objects

#    This scenario may bring back private fields in the future to test
  Scenario: Get a company after authenticating
    Given I have an authenticated user
    And The authenticated user has a company
    When I get the company data for myself
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.company.id"
    And The value of the "payload.company.id" field returned is of type "string"
    And The string length of the "payload.company.id" field is "16"
    And Finally, I clean up my objects

  Scenario: Fail to get a company with an invalid id
    Given I have an authenticated user
    And The authenticated user has a company
    When I get the company data for a fake company
    Then I get a status code "404"
    And I get back a valid json object
    And The response contains a field named "error.code"
    And The value of the "error.code" field returned is of type "string"
    And The value of the "error.code" field is "Api.CompanyController.indexGetAction.1"
    And Finally, I clean up my objects
