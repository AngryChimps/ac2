Feature: booking-delete
  In order to delete a booking
  As a user
  I need to be able to delete the booking information

  Scenario: Get a booking object
    Given I have an authenticated user
    And I have a valid availability array starting "tomorrow" at "08:00" until "tomorrow" at "09:00"
    And I post the availability array
    And I have a valid booking array starting "tomorrow" at "08:00" until "tomorrow" at "09:00"
    And I post the booking array
    When I delete the test booking
    Then I get a status code "200"
    And I get back a valid json object
    And Finally, I clean up my objects
