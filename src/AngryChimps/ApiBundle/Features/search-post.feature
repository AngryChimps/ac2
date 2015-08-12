Feature: search-post
  In order to search for a provider
  As a user
  I need to be able to post a search object

  Scenario: Post a search object
    Given I get a new session
    And I create a new member
    And I create a new company
    And I create a new location
    And I have a sample request array for the "location" api, "patch" method
    And I change the request array "payload.status" field to "1"
    And I send a "patch" message to the "location" api with id from the "location.id" variable
    When I search for locations
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.count"
    And The value of the "payload.count" field returned is of type "int"
    And The response contains a field named "payload.results"
    And The value of the "payload.results" field returned is of type "array"
    And Finally, I clean up my objects
