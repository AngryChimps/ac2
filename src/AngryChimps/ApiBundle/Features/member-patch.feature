Feature: member-patch
  In order to modify my member information
  As a user
  I need to be able to patch to the member endpoint

  Scenario: Get a new session token
    Given I get a new session
    And I create a new member
    And I have a sample request array for the "member" api, "patch" method
    And I change the request array "payload.first" field to "Sue"
    And I send a "patch" message to the "member" api with id from the "member.id" variable
    Then I get a status code "200"
    And I get back a valid json object
    And I send a "get" message to the "member" api with id from the "member.id" variable
    And The response contains a field named "payload.member.first"
    And The value of the "payload.member.first" field returned is of type "string"
    And The value of the "payload.member.first" field is "Sue"
    And Finally, I clean up my objects
