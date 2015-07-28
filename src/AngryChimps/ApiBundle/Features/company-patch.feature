Feature: company-patch
  In order to change my company information
  As a user
  I need to be able to patch a company object

  Scenario: Successfully modify a company object
    Given I get a new session
    And I create a new member
    And I create a new company
    And I have a sample request array for the "company" api, "patch" method
    And I change the request array "payload.name" field to "Joe's Crabshack"
    And I send a "patch" message to the "company" api with id from the "company.id" variable
    Then I get a status code "200"
    And I get back a valid json object
    And Finally, I clean up my objects
