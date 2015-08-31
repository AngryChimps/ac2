Feature: review-post
  In order to create a review location
  As a user
  I need to be able to post a new review object

  Scenario: Successfully create a review object
    Given I get a new session
    And I create a new member
    And I create a new company
    And I create a new location
    And I create a new staff member
    When I create a new review
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.review.id"
    And The value of the "payload.review.id" field returned is of type "string"
    And The string length of the "payload.review.id" field is "40"
    And The response fields are shown in the documentation for the "review" entity "post" method
    And No undocumented fields are returned in the response for the "review" entity "post" method
    And Finally, I clean up my objects
