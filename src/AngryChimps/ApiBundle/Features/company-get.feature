Feature: company-get
  In order to display information about a company
  As a user
  I need to be able to get a company object

  Scenario: Get a company with a valid id without authenticating
    Given I get a new session
    And I create a new member
    And I create a new company
    When I get the company data for the company
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.company.id"
    And The value of the "payload.company.id" field returned is of type "string"
    And The string length of the "payload.company.id" field is "40"
    And Finally, I clean up my objects