Feature: City weather summary
  In order to see current weather and trend for a city
  As an API client
  I want to call /api/weather and get a JSON response

  Scenario: Basic weather summary for Sofia
    When I request the city weather for "Sofia"
    Then the response status code should be 200
    And the JSON response should contain JSON with at least:
      """
      {
        "city": "Sofia"
      }
      """

  Scenario: Weather summary structure for a valid city
    When I request the city weather for "Burgas"
    Then the response status code should be 200
    And the JSON response should have string field "city"
    And the JSON response should have numeric field "current"
    And the JSON response should have field "average"
    And the JSON response should have field "trend"
    And the JSON response should have string field "trend.direction"
    And the JSON response should have numeric field "trend.delta"
    And the JSON response should have string field "trend.label"

  Scenario: Missing city parameter should return 400
    When I request the city weather with no parameters
    Then the response status code should be 400

