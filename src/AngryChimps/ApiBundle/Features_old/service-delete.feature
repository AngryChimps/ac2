Feature: service-delete
  In order to remove a service from the company
  As a user
  I need to be able to mark a service deleted

  Scenario: Delete a service successfully
    Given I have an authenticated user
    And I have a test company
    And The test company has a test location
    And The test location has a test service
    When I delete the test service
    Then I get a status code "200"
    And Finally, I clean up my objects
