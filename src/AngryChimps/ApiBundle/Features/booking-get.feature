Feature: booking-get
  In order for a service provider to view booking details
  As a user
  I need to be able to get the booking information

  Scenario: Get a booking object
    Given I have an authenticated user
    And I have a valid availability array starting "tomorrow" at "08:00" until "tomorrow" at "09:00"
    And I post the availability array
    And I have a valid booking array starting "tomorrow" at "08:00" until "tomorrow" at "09:00"
    And I post the booking array
    When I get the test booking
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.booking.id"
    And The value of the "payload.booking.id" field returned is of type "string"
    And The string length of the "payload.booking.id" field is "32"
    And Finally, I clean up my objects
