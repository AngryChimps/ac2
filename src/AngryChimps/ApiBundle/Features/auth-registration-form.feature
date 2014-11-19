Feature: auth-registration-form
  In order to perform privileged tasks
  As a user
  I need to be able to register using a form

  Scenario: Register a valid user using form authentication
    Given I have a valid new user object
    When I register a new user
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.member.id"
    And The value of the "payload.member.id" field returned is of type "string"
    And The string length of the "payload.member.id" field is "16"