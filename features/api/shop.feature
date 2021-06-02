Feature: Shop
  In order to manage my shops
  As an API user
  I need to be able to manage my shops

  Background:
    Given there is a user "tuyo-admin" with password "tuyo123"
    And I authenticate with token for user "tuyo-admin@foo.com"
    And the following shops exist:
      | id                       | name         | configurations                               |
      | 5bf97ebf2c02b1000f7d21a5 | Media expert | {"test_1" : {"test_1-1" : {"value": "1-1"}}} |

  Scenario: GET one shop
    When I request "GET /api/shops/5bf97ebf2c02b1000f7d21a5"
    Then the response status code should be 200
    And the following properties should exist:
      """
      configurations
      """
    And the "name" property should exist

  Scenario: GET a collection of shops
    Given the following shops exist:
      | id                       | name                         | phone        | address                                 |
      | 5bf97ebf2c02b1000f7d21a1 | Leroy Merlin Poznań Posnania | 61-679-4800  | "ul. Pleszewska 1 61-136 Poznań"        |
      | 5bf97ebf2c02b1000f7d21a2 | Leroy Merlin Bełchatów       | 44 631 78 00 | "ul. Armii Krajowej 9 97-400 Bełchatów" |
    When I request "GET /api/shops"
    Then the response status code should be 200
    And the "_payload" property should be an array
    And the "_payload" property should contain 3 items
    And the "_payload.1.name" property should equal "Leroy Merlin Poznań Posnania"

  Scenario: Update a shop with incomplete data
    Given I have the payload:
      """
        {
          "name" : "MediaMarkt Poznań - Avenida",
          "phone" : "505505505",
          "address" : "ul. Stanisława Matyi 2 61-586 Poznań",
          "email" : "info@mediamarkt.pl"
        }
      """
    When I request "PUT /api/shops/5bf97ebf2c02b1000f7d21a5"
    Then the response status code should be 400

  Scenario: Update a shop with invalid data
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
    When I request "PUT /api/shops/5bf97ebf2c02b1000f7d21a5"
    Then the response status code should be 400

  Scenario: Update a shop
    Given the following clients exist:
      | id                       | name                | nip       |
      | 6bf97ebf2c02b1000f7d21a1 | Media Markt Sp. zoo | 123456789 |
    Given I have the payload:
      """
        {
          "name" : "MediaMarkt Poznań - Avenida",
          "phone" : "505505505",
          "address" : "ul. Stanisława Matyi 2 61-586 Poznań",
          "email" : "info@mediamarkt.pl",
          "client" : "6bf97ebf2c02b1000f7d21a1"
        }
      """
    When I request "PUT /api/shops/5bf97ebf2c02b1000f7d21a5"
    Then the response status code should be 200

  Scenario: Update a non-existing shop
    Given I have the payload:
      """
        {
          "name" : "MediaMarkt Poznań - Avenida"
        }
      """
    When I request "PUT /api/shops/5bf97ebf2c02b1000f7d0000"
    Then the response status code should be 404

  Scenario: Create a shop
    Given the following clients exist:
      | id                       | name                | nip       |
      | 6bf97ebf2c02b1000f7d21a1 | Media Markt Sp. zoo | 123456789 |
    And I have the payload:
      """
        {
          "name" : "MediaMarkt Poznań - Avenida",
          "phone" : "799353535",
          "address" : "ul. Stanisława Matyi 2 61-586 Poznań",
          "email" : "info@mediamarkt.pl",
          "configurations" : {
              "test-1" : "test-1value",
              "test-2" : {
                "test-21" : "value21"
              }
           },
           "client" : "6bf97ebf2c02b1000f7d21a1"
        }
      """
    When I request "POST /api/shops"
    Then the response status code should be 201
    And the "Location" header should exist

  Scenario: Delete a shop
    When I request "DELETE /api/shops/5bf97ebf2c02b1000f7d21a5"
    Then the response status code should be 204

  Scenario: Delete a non existent shop
    When I request "DELETE /api/shops/5bf97ebf2c02b1000f7d0000"
    Then the response status code should be 404