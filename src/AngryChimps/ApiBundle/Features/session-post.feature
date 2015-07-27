Feature: session-post
  In order to make any api calls
  As a user
  I need to be able to get a session token

  Scenario: Get a new session token
    When I get a new session
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.session.id"
    And The value of the "payload.session.id" field returned is of type "string"
    And The string length of the "payload.session.id" field is "80"
    And Finally, I clean up my objects
