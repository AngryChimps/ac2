Feature: session-get
  In order to make any api calls
  As a user
  I need to be able to get a session token

  Scenario: Get a new session token
    When I get a new session token
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.session_id"
    And The value of the "payload.session_id" field returned is of type "string"
    And The string length of the "payload.session_id" field is "32"
    And Finally, I clean up my objects
