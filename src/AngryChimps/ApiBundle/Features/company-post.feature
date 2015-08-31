Feature: company-post
  In order to create a company
  As a user
  I need to be able to post a new company object

  Scenario: Successfully create a company object
    Given I get a new session
    And I create a new member
    When I create a new company
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.company.id"
    And The value of the "payload.company.id" field returned is of type "string"
    And The string length of the "payload.company.id" field is "40"
    And The response fields are shown in the documentation for the "company" entity "post" method
    And No undocumented fields are returned in the response for the "company" entity "post" method
    And Finally, I clean up my objects
