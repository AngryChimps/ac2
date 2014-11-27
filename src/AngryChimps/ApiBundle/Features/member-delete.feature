Feature: member-delete
  In order to delete my account
  As a user
  I need to be able to delete my account

  Scenario: Successfully delete a member object
    Given I have an authenticated user
    When I delete the authenticated user
    Then I get a status code "200"
    And The authenticated user's "status" field is "2"
    And Finally, I clean up my objects
