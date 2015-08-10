Feature: review-get
  In order to display information about a review member
  As a user
  I need to be able to get a review object

  Scenario: Get a review member object
    Given I get a new session
    And I create a new member
    And I create a new company
    And I create a new location
    And I create a new staff member
    And I create a new review
    When I get the "review" data for id "review.id"
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.review.id"
    And The value of the "payload.review.id" field returned is of type "string"
    And The string length of the "payload.review.id" field is "40"
    And Finally, I clean up my objects
