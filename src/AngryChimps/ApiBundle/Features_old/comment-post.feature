Feature: comment-post
  In order for a user to comment on a company
  As a user
  I need to be able to post the comment

  Scenario: Post a booking exactly matching an availability
    Given I have an authenticated user
    And I have a valid comment array
    When I post the comment array
    Then I get a status code "200"
    And I get back a valid json object
    And the test company has "1" comment
    And Finally, I clean up my objects

