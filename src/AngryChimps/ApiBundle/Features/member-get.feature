Feature: member-get
  In order to display information about a member
  As a user
  I need to be able to get a member object

  Scenario: Get a member with a valid id without authenticating
    Given I have a test user
    And I get a new session token
    When I get the member data for myself
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.member.id"
    And The value of the "payload.member.id" field returned is of type "string"
    And The string length of the "payload.member.id" field is "16"
    And The response does not contain a field named "payload.member.email"
    And Finally, I clean up my objects
