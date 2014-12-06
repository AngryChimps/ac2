Feature: signup-registerProviderCompany
  In order to place my first provider ad
  As a user
  I need to be able complete the signup process by supplying company and other information

  Scenario: Signup a new user
    Given I get a new session token
    And I have a valid signup ad array
    And I register a provider ad
    And I have a valid signup company array
    And I register a provider ad company
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.ad.id"
    And The value of the "payload.ad.id" field returned is of type "string"
    And The string length of the "payload.ad.id" field is "16"
    And Finally, I clean up my objects

