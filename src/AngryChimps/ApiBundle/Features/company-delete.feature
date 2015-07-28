Feature: company-delete
  In order to remove my company from the system
  As a user
  I need to be able to mark the company as deleted

  Scenario: Successfully delete a company object
    Given I get a new session
    And I create a new member
    And I create a new company
    And I send a "delete" message to the "company" api with id from the "company.id" variable
    Then I get a status code "200"
    When I get the company data for the company
    Then I get a status code "404"
    And Finally, I clean up my objects

