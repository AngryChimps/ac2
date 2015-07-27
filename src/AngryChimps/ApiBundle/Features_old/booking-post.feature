Feature: booking-post
  In order for a user to create a booking
  As a user
  I need to be able to post the booking information

  Scenario: Post a booking exactly matching an availability
    Given I have an authenticated user
    And I have a valid availability array starting "tomorrow" at "08:00" until "tomorrow" at "09:00"
    And I post the availability array
    And I have a valid booking array starting "tomorrow" at "08:00" until "tomorrow" at "09:00"
    When I post the booking array
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.booking.id"
    And The value of the "payload.booking.id" field returned is of type "string"
    And The string length of the "payload.booking.id" field is "32"
    And Finally, I clean up my objects

  Scenario: Post a booking exceeding the length of the availability window
    Given I have an authenticated user
    And I have a valid availability array starting "tomorrow" at "08:00" until "tomorrow" at "09:00"
    And I post the availability array
    And I have a valid booking array starting "tomorrow" at "08:00" until "tomorrow" at "08:30"
    When I post the booking array
    Then I get a status code "400"
    And I get back a valid json object
    And The response contains a field named "error.code"
    And The value of the "error.code" field returned is of type "string"
    And The value of the "error.code" field is "Api.BookingController.indexPostAction.5"
    And Finally, I clean up my objects

