Feature: calendar-get
  In order to display information about a calendar
  As a user
  I need to be able to get a calendar object

  Scenario: Get a calendar with a valid id without authenticating
    Given I get a new session token
    And I have a valid signup ad array
    And I register a provider ad
    And I have a valid signup company array
    And I register a provider ad company
    And Another user has a calendar
    When I get the calendar data for the calendar
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.calendar.id"
    And The value of the "payload.calendar.id" field returned is of type "string"
    And The string length of the "payload.calendar.id" field is "32"
    And Finally, I clean up my objects

#  Calendar doesn't have any bookings yet...need to build that
#  Scenario: Get a calendar after authenticating
#    Given I get a new session token
#    And I have a valid signup ad array
#    And I register a provider ad
#    And I have a valid signup company array
#    And I register a provider ad company
#    And The authenticated user has a calendar
#    When I get the calendar data for myself
#    Then I get a status code "200"
#    And I get back a valid json object
#    And The response contains a field named "payload.calendar.bookings"
#    And The value of the "payload.calendar.bookings" field returned is of type "array"
#    And Finally, I clean up my objects
