Feature: company-put
  In order to change my company information
  As a user
  I need to be able to modify and save a company object

  Scenario: Successfully modify a company object
    Given I have an authenticated user
    And I have a valid new company array
    And I create a test company
    And I change the test companys "name" field to "Killer Co."
    When I put changes to the test company
    Then I get a status code "200"
    And I get back a valid json object
    And If I reload the test company
    And The value of the "name" field of the test company is "Killer Co."
    And Finally, I clean up my objects

  Scenario: Attempt to modify a company object with invalid data
    Given I have an authenticated user
    And I have a valid new company array
    And I create a test company
    And I change the test companys "name" field to "a"
    When I put changes to the test company
    Then I get a status code "400"
    And I get back a valid json object
    And The response contains a field named "error.code"
    And The value of the "error.code" field returned is of type "string"
    And The value of the "error.code" field is "Api.CompanyController.indexPutAction.3"
    And Finally, I clean up my objects
