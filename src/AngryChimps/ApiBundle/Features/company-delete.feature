Feature: company-delete
  In order to remove my company from the system
  As a user
  I need to be able to mark the company as deleted

  Scenario: Successfully delete a company object
    Given I have an authenticated user
    And The authenticated user has a company
    When I delete the test company
    Then I get a status code "200"
    And Finally, I clean up my objects

  Scenario: Fail to delete a non-existent company
    Given I have an authenticated user
    When I delete a non-existent company
    Then I get a status code "404"
    And I get back a valid json object
    And The response contains a field named "error.code"
    And T he value of the "error.code" field returned is of type "string"
    And The value of the "error.code" field is "Api.CompanyController.indexDeleteAction.1"
    And Finally, I clean up my objects

  Scenario: Fail to delete a company owned by someone else
    Given I have an authenticated user
    And I have a test company
    And I change the test companys "administer_member_ids" field to an empty array
    And I save changes to the test company
    When I delete the test company
    Then I get a status code "403"
    And I get back a valid json object
    And The response contains a field named "error.code"
    And The value of the "error.code" field returned is of type "string"
    And The value of the "error.code" field is "Api.CompanyController.indexDeleteAction.2"
    And Finally, I clean up my objects

