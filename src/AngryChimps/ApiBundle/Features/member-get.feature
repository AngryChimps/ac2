Feature: member-get
  In order to get information about a member
  As a user
  I need to be able to read the member endpoint

  Scenario: Get a new session token
    When I get a new session
    And I create a new member
    And I get the authenticated member's information
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.member.id"
    And The value of the "payload.member.id" field returned is of type "string"
    And The string length of the "payload.member.id" field is "40"
    And The response contains a field named "payload.member.email"
    And The value of the "payload.member.email" field returned is of type "string"
    And The string length of the "payload.member.email" field is greater than zero
    And Finally, I clean up my objects
