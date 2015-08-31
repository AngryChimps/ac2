Feature: staff-post
  In order to create a staff member
  As a user
  I need to be able to post a new staff object

  Scenario: Successfully create a staff object
    Given I get a new session
    And I create a new member
    And I create a new company
    And I create a new location
    When I create a new staff member
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.staff.id"
    And The value of the "payload.staff.id" field returned is of type "string"
    And The string length of the "payload.staff.id" field is "40"
    And The response fields are shown in the documentation for the "staff" entity "post" method
    And No undocumented fields are returned in the response for the "staff" entity "post" method
    And Finally, I clean up my objects
