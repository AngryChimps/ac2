Feature: location-get
  In order to display information about a location
  As a user
  I need to be able to get a location object

  Scenario: Get a location with a valid id without authenticating
    Given I get a new session
    And I create a new member
    And I create a new company
    And I create a new location
    And I create a new staff member
    And I create a new review
    And I wait "2" seconds
    When I get the "location" data for id "location.id" with GET param string "company&staff_count=5&review_count=5"
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.location.id"
    And The value of the "payload.location.id" field returned is of type "string"
    And The string length of the "payload.location.id" field is "40"
    And Finally, I clean up my objects
