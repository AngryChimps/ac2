Feature: auth-registration-form
  In order to perform privileged tasks
  As a user
  I need to be able to register using a form

#  Scenario: Register a valid user using form authentication
#    Given I have a valid new user array
#    And I get a new session token
#    When I register a new user
#    Then I get a status code "200"
#    And I get back a valid json object
#    And The response contains a field named "payload.member.id"
#    And The value of the "payload.member.id" field returned is of type "string"
#    And The string length of the "payload.member.id" field is "32"
#    And The response contains a field named "payload.auth_token"
#    And The value of the "payload.auth_token" field returned is of type "string"
#    And The string length of the "payload.auth_token" field is "32"
#    And Finally, I clean up my objects
#
#  Scenario: Attempt registration of a new member with the email of an active member
#    Given I have a valid new user array
#    And I get a new session token
#    When I register a new user
#    And I register a new user
#    Then I get a status code "400"
#    And The value of the "error.code" field returned is of type "string"
#    And The value of the "error.code" field is "Api.MemberController.indexPostAction.1"
#    And Finally, I clean up my objects
#
#  Scenario: Attempt registration of a new member with invalid data
#    Given I have a valid new user array
#    And I get a new session token
#    And I change the "payload.name" field's value of the request object to "a"
#    When I register a new user
#    Then I get a status code "400"
#    And The value of the "error.code" field returned is of type "string"
#    And The value of the "error.code" field is "Api.MemberController.indexPostAction.2"
#    And Finally, I clean up my objects
