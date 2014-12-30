Feature: availability-post
  In order for a provider to add an availability to their calendar
  As a provider
  I need to be able to post the availability information

  Scenario: Post a non-conflicting availability successfully
    Given I have an authenticated user
    And I have a test company
    And The test company has a test location
    And The test location has a test calendar
    And The test calendar has a test availability
    And I have a valid non-conflicting availability array
    When I post the availability array
    Then I get a status code "200"
    And I get back a valid json object
    And I reload the test calendar
    And The test calendar has "2" availabilities
    And Finally, I clean up my objects

  Scenario: Post a an availability which overlaps the end of another availability
    Given I have an authenticated user
    And I have a test company
    And The test company has a test location
    And The test location has a test calendar
    And The test calendar has a test availability
    And I have a valid availability array starting "tomorrow" at "08:00" until "tomorrow" at "09:30"
    When I post the availability array
    Then I get a status code "200"
    And I get back a valid json object
    And I reload the test calendar
    And The test calendar has "1" availabilities
    And The calendar's first availability starts "tomorrow" at "08:00" and ends "tomorrow" at "12:00"
    And Finally, I clean up my objects

  Scenario: Post a an availability which overlaps the start of another availability
    Given I have an authenticated user
    And I have a test company
    And The test company has a test location
    And The test location has a test calendar
    And The test calendar has a test availability
    And I have a valid availability array starting "tomorrow" at "11:00" until "tomorrow" at "13:30"
    When I post the availability array
    Then I get a status code "200"
    And I get back a valid json object
    And I reload the test calendar
    And The test calendar has "1" availabilities
    And The calendar's first availability starts "tomorrow" at "09:00" and ends "tomorrow" at "13:30"
    And Finally, I clean up my objects

  Scenario: Post a an availability which is overlapped entirely by another availability
    Given I have an authenticated user
    And I have a test company
    And The test company has a test location
    And The test location has a test calendar
    And The test calendar has a test availability
    And I have a valid availability array starting "tomorrow" at "10:00" until "tomorrow" at "11:30"
    When I post the availability array
    Then I get a status code "200"
    And I get back a valid json object
    And I reload the test calendar
    And The test calendar has "1" availabilities
    And The calendar's first availability starts "tomorrow" at "09:00" and ends "tomorrow" at "12:00"
    And Finally, I clean up my objects

  Scenario: Post a an availability which encompasses another availability
    Given I have an authenticated user
    And I have a test company
    And The test company has a test location
    And The test location has a test calendar
    And The test calendar has a test availability
    And I have a valid availability array starting "tomorrow" at "08:00" until "tomorrow" at "14:30"
    When I post the availability array
    Then I get a status code "200"
    And I get back a valid json object
    And I reload the test calendar
    And The test calendar has "1" availabilities
    And The calendar's first availability starts "tomorrow" at "08:00" and ends "tomorrow" at "14:30"
    And Finally, I clean up my objects

  Scenario: Post a an availability which joins two other availabilities
    Given I have an authenticated user
    And I have a test company
    And The test company has a test location
    And The test location has a test calendar
    And The test calendar has an availability starting "tomorrow" at "09:00" until "tomorrow" at "12:00"
    And The test calendar has an availability starting "tomorrow" at "13:00" until "tomorrow" at "14:00"
    And I have a valid availability array starting "tomorrow" at "12:00" until "tomorrow" at "13:00"
    When I post the availability array
    Then I get a status code "200"
    And I get back a valid json object
    And I reload the test calendar
    And The test calendar has "1" availabilities
    And The calendar's first availability starts "tomorrow" at "09:00" and ends "tomorrow" at "14:00"
    And Finally, I clean up my objects
