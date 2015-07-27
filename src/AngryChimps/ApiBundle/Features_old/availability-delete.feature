Feature: availability-delete
  In order for a provider to mark a time as unavailable
  As a provider
  I need to be able to delete an availability

  Scenario: Delete an availability which does not exist
    Given I have an authenticated user
    And I have a test company
    And The test company has a test location
    And The test location has a test calendar
    And The test calendar has an availability starting "tomorrow" at "09:00" until "tomorrow" at "12:00"
    And I have a valid availability array starting "tomorrow" at "08:00" until "tomorrow" at "08:30"
    When I delete the availability
    Then I get a status code "200"
    And I get back a valid json object
    And I reload the test calendar
    And The test calendar has "1" availabilities
    And The calendar's first availability starts "tomorrow" at "09:00" and ends "tomorrow" at "12:00"
    And Finally, I clean up my objects

  Scenario: Delete an availability which exactly the same as the posted times
    Given I have an authenticated user
    And I have a test company
    And The test company has a test location
    And The test location has a test calendar
    And The test calendar has an availability starting "tomorrow" at "09:00" until "tomorrow" at "12:00"
    And I have a valid availability array starting "tomorrow" at "09:00" until "tomorrow" at "12:00"
    When I delete the availability
    Then I get a status code "200"
    And I get back a valid json object
    And I reload the test calendar
    And The test calendar has "0" availabilities
    And Finally, I clean up my objects

  Scenario: Delete an availability which encompasses another availability
    Given I have an authenticated user
    And I have a test company
    And The test company has a test location
    And The test location has a test calendar
    And The test calendar has an availability starting "tomorrow" at "09:00" until "tomorrow" at "12:00"
    And I have a valid availability array starting "tomorrow" at "08:00" until "tomorrow" at "13:00"
    When I delete the availability
    Then I get a status code "200"
    And I get back a valid json object
    And I reload the test calendar
    And The test calendar has "0" availabilities
    And Finally, I clean up my objects

  Scenario: Delete an availability which overlaps the end of another availability
    Given I have an authenticated user
    And I have a test company
    And The test company has a test location
    And The test location has a test calendar
    And The test calendar has an availability starting "tomorrow" at "09:00" until "tomorrow" at "12:00"
    And I have a valid availability array starting "tomorrow" at "08:00" until "tomorrow" at "9:30"
    When I delete the availability
    Then I get a status code "200"
    And I get back a valid json object
    And I reload the test calendar
    And The test calendar has "1" availabilities
    And The calendar's first availability starts "tomorrow" at "09:30" and ends "tomorrow" at "12:00"
    And Finally, I clean up my objects

  Scenario: Delete an availability which overlaps the beginning of another availability
    Given I have an authenticated user
    And I have a test company
    And The test company has a test location
    And The test location has a test calendar
    And The test calendar has an availability starting "tomorrow" at "09:00" until "tomorrow" at "12:00"
    And I have a valid availability array starting "tomorrow" at "11:00" until "tomorrow" at "12:30"
    When I delete the availability
    Then I get a status code "200"
    And I get back a valid json object
    And I reload the test calendar
    And The test calendar has "1" availabilities
    And The calendar's first availability starts "tomorrow" at "09:00" and ends "tomorrow" at "11:00"
    And Finally, I clean up my objects

