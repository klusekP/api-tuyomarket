Feature: User API
  In order to manage my users
  As an API user
  I need to be able to manage my users

  Background:
    Given there is a user "tuyo-admin" with password "tuyo123"
    And I authenticate with token for user "tuyo-admin@foo.com"
    And the following users exist:
      | id                       | username  | email             | plainPassword |
      | 7bf97ebf2c02b1000f7d21a5 | test-user | test-user@foo.com | "test-user"   |

  Scenario: GET one user
    When I request "GET /api/users/7bf97ebf2c02b1000f7d21a5"
    Then the response status code should be 200
    And the following properties should exist:
      """
      username
      email
      """
    And the "password" property should not exist

  Scenario: GET a collection of users
    When I request "GET /api/users"
    Then the response status code should be 200
    And the "_payload" property should be an array
    And the "_payload" property should contain 2 items
    And the "_payload.1.username" property should equal "test-user"

  Scenario: Update a user with invalid data
    Given I have the payload:
      """
        {
          "phone" : "505505505"
        }
      """
    When I request "PUT /api/users/7bf97ebf2c02b1000f7d21a5"
    Then the response status code should be 400

  Scenario: Update a user
    Given I have the payload:
      """
        {
          "plainPassword" : "noweTajneHaslo"
        }
      """
    When I request "PUT /api/users/7bf97ebf2c02b1000f7d21a5"
    Then the response status code should be 200

  Scenario: Update a non-existing user
    Given I have the payload:
      """
        {
          "username" : "something"
        }
      """
    When I request "PUT /api/users/5bf97ebf2c02b1000f7d0000"
    Then the response status code should be 404

  Scenario: Create a user for an existing client
    Given the following clients exist:
      | id                       | name                   | email              |
      | 6bf97ebf2c02b1000f7d21a5 | Media expert Sp z o.o. | info@mediamarkt.pl |
    And I have the payload:
      """
        {
          "username" : "snd-test",
          "email" : "snd-test@foo.com",
          "plainPassword" : "verySecretStuff",
          "client" : "6bf97ebf2c02b1000f7d21a5"
        }
      """
    When I request "POST /api/users"
    Then the response status code should be 201
    And the "Location" header should exist

  Scenario: Try to create a user a non-existing client
    Given I have the payload:
      """
        {
          "username" : "snd-test",
          "email" : "snd-test@foo.com",
          "plainPassword" : "verySecretStuff",
          "client" : "00007ebf2c02b1000f7d21a5"
        }
      """
    When I request "POST /api/users"
    Then the response status code should be 400
    And the "message" property should be a string equalling "Validation Failed"

  Scenario: Create a new user with embedded client information
    Given I have the payload:
      """
        {
          "username" : "snd-test",
          "email" : "snd-test@foo.com",
          "plainPassword" : "verySecretStuff",
          "client" : {
            "name" : "Biedrona",
            "phone" : "789456123",
            "address" : "ul. Stanisława Matyi 2 61-586 Poznań",
            "email" : "info@biedrona.pl",
            "nip" : "456-7889-7895",
            "regon" : "123333222111",
            "short" : "Bied"
          }
        }
      """
    When I request "POST /api/users"
    Then the response status code should be 201
    And the "Location" header should exist


  Scenario: Try to create a new user with embedded client information which is invalid
    Given I have the payload:
      """
        {
          "username" : "snd-test",
          "email" : "snd-test@foo.com",
          "plainPassword" : "verySecretStuff",
          "client" : {
            "phone" : "789456123",
            "address" : "ul. Stanisława Matyi 2 61-586 Poznań",
            "email" : "info@biedrona.pl",
            "nip" : "456-7889-7895",
            "regon" : "123333222111",
            "short" : "Bied"
          }
        }
      """
    When I request "POST /api/users"
    Then the response status code should be 400
    And the "message" property should be a string equalling "Validation Failed"

  Scenario: Create a user without client
    Given I have the payload:
      """
        {
          "username" : "snd-test",
          "email" : "snd-test@foo.com",
          "plainPassword" : "verySecretStuff",
          "client" : "6bf97ebf2c02b1000f7d21a5"
        }
      """
    When I request "POST /api/users"
    Then the response status code should be 400
    And the "message" property should be a string equalling "Validation Failed"

  Scenario: Delete a user
    When I request "DELETE /api/users/7bf97ebf2c02b1000f7d21a5"
    Then the response status code should be 204

  Scenario: Delete a non existent user
    When I request "DELETE /api/users/5bf97ebf2c02b1000f7d0000"
    Then the response status code should be 404