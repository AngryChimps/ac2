Feature: comment-get
  In order for a user to view comments for a company
  As a user
  I need to be able to get comments for a company

  Scenario: Get a comment successfully
    Given I have an authenticated user
    And I have a valid comment array
    And I post the comment array
    When I get the test company's comments
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.count"
    And The value of the "payload.count" field is "1"
    And Finally, I clean up my objects

