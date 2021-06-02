Feature: Authentication
  In order to access protected resource
  As an API user
  I need to be able to authenticate

  Scenario: Create a client without authentication
    When I request "POST /api/clients"
    Then the response status code should be 401
    And the "message" property should equal "Full authentication is required to access this resource."
    And the "Content-Type" header should be "application/json"
    
  Scenario: Check if doc api is available
    When I request "GET /api/doc"
    Then the response status code should be 200

  Scenario: Authenticate
    Given there is a user "tuyo-admin" with password "tuyo123"
    And I have the payload:
    """
      {
        "auth" : {
          "username" : "tuyo-admin@foo.com",
          "password" : "tuyo123"
        }
      }
    """
    When I request "POST /api/tokens"
    Then the response status code should be 200
    And the "token" property should exist

  Scenario: Wrong password
    Given there is a user "tuyo-admin" with password "tuyo123"
    And I have the payload:
    """
      {
        "auth" : {
          "username" : "tuyo-admin@foo.com",
          "password" : "123wrong123"
        }
      }
    """
    When I request "POST /api/tokens"
    Then the response status code should be 401
    And the "token" property should not exist

  Scenario: No user
    Given I have the payload:
    """
      {
        "auth" : {
          "username" : "tuyo-admin@wrong.com",
          "password" : "123wrong123"
        }
      }
    """
    When I request "POST /api/tokens"
    Then the response status code should be 404
    And the "token" property should not exist