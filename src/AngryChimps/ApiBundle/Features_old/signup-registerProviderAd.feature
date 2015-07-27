Feature: signup-registerProviderAd
  In order to place my first provider ad
  As a user
  I need to be able sign up and furnish ad information

  Scenario: Signup a new user
    Given I get a new session token
    And I have a valid signup ad array
    When I register a provider ad
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.member.id"
    And The value of the "payload.member.id" field returned is of type "string"
    And The string length of the "payload.member.id" field is "32"
    And Finally, I clean up my objects

