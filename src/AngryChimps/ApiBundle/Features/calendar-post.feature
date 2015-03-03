Feature: calendar-post
  In order to create a new calendar
  As a user
  I need to be able to post a calendar object

  Scenario: Get a calendar with a valid id without authenticating
    Given I have a test user
#    And The authenticated user has a company
#    And The test company has a test location
    And I have a valid new calendar array
    When I post the calendar data for the calendar
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.calendar_id.id"
    And The value of the "payload.calendar_id.id" field returned is of type "string"
    And The string length of the "payload.calendar_id.id" field is "32"
    And Finally, I clean up my objects
