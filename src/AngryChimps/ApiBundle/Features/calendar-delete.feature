Feature: calendar-put
  In order to modify a calendar
  As a user
  I need to be able to put a calendar object

  Scenario: Get a calendar with a valid id without authenticating
    Given I get a new session token
    And I have a valid signup ad array
    And I register a provider ad
    When I delete the test calendar
    Then I get a status code "200"
    And Finally, I clean up my objects
