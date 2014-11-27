Feature: auth-login-form
  In order to perform privileged tasks
  As a user
  I need to be able to log in using a form

  Scenario: Log in a valid user using form authentication
    Given I have a test user
    And I get a new session token
    And I have a valid form login array
    When I log in
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.member.id"
    And The value of the "payload.member.id" field returned is of type "string"
    And The string length of the "payload.member.id" field is "16"
    And Finally, I clean up my objects

  Scenario: Attempt log in with invalid email
    Given I have a test user
    And I get a new session token
    And I have a valid form login array
    And I change the "payload.email" field's value of the request object to "a"
    When I log in
    Then I get a status code "400"
    And The value of the "error.code" field returned is of type "string"
    And The value of the "error.code" field is "Api.AuthController.loginAction.1"
    And Finally, I clean up my objects

  Scenario: Attempt log in with invalid password
    Given I have a test user
    And I get a new session token
    And I have a valid form login array
    And I change the "payload.password" field's value of the request object to "a"
    When I log in
    Then I get a status code "400"
    And The value of the "error.code" field returned is of type "string"
    And The value of the "error.code" field is "Api.AuthController.loginAction.1"
    And Finally, I clean up my objects
