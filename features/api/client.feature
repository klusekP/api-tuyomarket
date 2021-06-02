Feature: Client API
  In order to manage my clients
  As an API user
  I need to be able to manage my clients

  Background:
    Given there is a user "tuyo-admin" with password "tuyo123"
    And I authenticate with token for user "tuyo-admin@foo.com"
    And the following clients exist:
      | id                       | name                   | email              |
      | 6bf97ebf2c02b1000f7d21a5 | Media expert Sp z o.o. | info@mediamarkt.pl |

  Scenario: GET one client
    When I request "GET /api/clients/6bf97ebf2c02b1000f7d21a5"
    Then the response status code should be 200
    And the following properties should exist:
      """
      name
      email
      """
    And the "name" property should equal "Media expert Sp z o.o."

  Scenario: GET a collection of clients
    Given the following clients exist:
      | id                       | name                     | phone        | address                                 | nip |
      | 6bf97ebf2c02b1000f7d21a1 | Leroy Merlin Sp. z o.o.  | 61-679-4800  | "ul. Pleszewska 1 61-136 Poznań"        | 123 |
      | 6bf97ebf2c02b1000f7d21a2 | Auchan Polska Sp. z o.o. | 44 631 78 00 | "ul. Armii Krajowej 9 97-400 Bełchatów" | 456 |
      | 6bf97ebf2c02b1000f7d21a3 | Żabka Polska Sp. z o.o.  | 61-679-4800  | "ul. Pleszewska 1 61-136 Poznań"        | 789 |
    When I request "GET /api/clients"
    Then the response status code should be 200
    And the "_payload" property should be an array
    And the "_payload" property should contain 4 items
    And the "_payload.2.name" property should equal "Auchan Polska Sp. z o.o."

  Scenario: Update a client with incomplete data
    Given I have the payload:
      """
        {
          "name" : "Media Expert Plus",
          "phone" : "40404040"
        }
      """
    When I request "PUT /api/clients/6bf97ebf2c02b1000f7d21a5"
    Then the response status code should be 400

  Scenario: Update a client with invalid data
    Given I have the payload:
      """
        {
          "name" : "MediaMarkt Poznań - Avenida",
          "phone" : "505505505",
          "address" : "ul. Stanisława Matyi 2 61-586 Poznań",
          "email" : "info@mediamarkt.pl",
          "client" : "111111111111"
        }
      """
    When I request "PUT /api/clients/6bf97ebf2c02b1000f7d21a5"
    Then the response status code should be 400

  Scenario: Update a client
    Given the following clients exist:
      | id                       | name                | nip       |
      | 6bf97ebf2c02b1000f7d21a1 | Media Markt Sp. zoo | 123456789 |
    Given I have the payload:
      """
         {
          "name" : "Biedrona",
          "phone" : "789456123",
          "address" : "ul. Stanisława Matyi 2 61-586 Poznań",
          "email" : "info@biedrona.pl",
          "nip" : "456-7889-7895",
          "regon" : "123333222111",
          "short" : "Bied"
        }
      """
    When I request "PUT /api/clients/123456789"
    Then the response status code should be 200

  Scenario: Update a non-existing client
    Given I have the payload:
      """
        {
          "name" : "Żaba",
        }
      """
    When I request "PUT /api/clients/5bf97ebf2c02b1000f7d0000"
    Then the response status code should be 404

  Scenario: Create a client
    And I have the payload:
      """
        {
          "name" : "Biedrona",
          "phone" : "789456123",
          "address" : "ul. Stanisława Matyi 2 61-586 Poznań",
          "email" : "info@biedrona.pl",
          "nip" : "456-7889-7895",
          "regon" : "123333222111",
          "short" : "Bied"
        }
      """
    When I request "POST /api/clients"
    Then the response status code should be 201
    And the "Location" header should exist

  Scenario: Create a client - violate unique nip constraint
    Given the following clients exist:
      | id                       | name                | nip       |
      | 6bf97ebf2c02b1000f7d21a1 | Media Markt Sp. zoo | 123456789 |
    And I have the payload:
      """
        {
          "name" : "Nowy klient ze zduplikowanym NIP'em",
          "phone" : "799353535",
          "address" : "ul. Stanisława Matyi 2 61-586 Poznań",
          "email" : "info@mediamarkt.pl",
          "nip" : "123456789"
        }
      """
    When I request "POST /api/clients"
    Then the response status code should be 400

  Scenario: Delete a client
    When I request "DELETE /api/clients/6bf97ebf2c02b1000f7d21a5"
    Then the response status code should be 204

  Scenario: Delete a non existent client
    When I request "DELETE /api/clients/00097ebf2c02b1000f7d21a5"
    Then the response status code should be 404