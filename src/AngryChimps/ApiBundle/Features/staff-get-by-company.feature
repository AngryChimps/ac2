Feature: staff-get-by-company
  In order to display information about staff members from a given company
  As a user
  I need to be able to get staff objects for that company

  Scenario: Get staff member objects for a company
    Given I get a new session
    And I create a new member
    And I create a new company
    And I create a new location
    And I create a new staff member
    And I wait "2" seconds
    When I get the "staff" data with GET param "company_id" and id "company.id"
    Then I get a status code "200"
    And I get back a valid json object
    And The response contains a field named "payload.staff"
    And The response field "payload.staff" has a count of "1"
    And Finally, I clean up my objects
