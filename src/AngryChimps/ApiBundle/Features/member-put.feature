Feature: member-put
  In order to change my member information
  As a user
  I need to be able to modify and save a member object

  Scenario: Successfully modify a member object
    Given I have an authenticated user
    And I change the authenticated users "name" field to "Bobby Jo"
    When I save changes to the authenticated user
    Then I get a status code "200"
    And I get back a valid json object
    And If I reload the authenticated user
    And The value of the "name" field of the authenticated user is "Bobby Jo"
    And Finally, I clean up my objects
