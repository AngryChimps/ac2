Feature: categories-get
  In order to display a list of categories to the user
  As a front end developer
  I need to be able to get a list of categories from the server

  Scenario: Get a list of categories
    Given I get a new session token
    When I get a list of categories from the server
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.categories"
    And The value of the "payload.categories" field returned is of type "array"
    And The "payload.categories" array is not empty
    And Finally, I clean up my objects
