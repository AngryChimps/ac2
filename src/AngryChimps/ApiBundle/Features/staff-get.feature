Feature: staff-get
  In order to display information about a staff member
  As a user
  I need to be able to get a staff object

  Scenario: Get a staff member object
    Given I get a new session
    And I create a new member
    And I create a new company
    And I create a new location
    And I create a new staff member
    When I get the "staff" data for id "staff.id"
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.staff.id"
    And The value of the "payload.staff.id" field returned is of type "string"
    And The string length of the "payload.staff.id" field is "40"
    And The response fields are shown in the documentation for the "staff" entity "get" method
    And No undocumented fields are returned in the response for the "staff" entity "get" method
    And Finally, I clean up my objects
