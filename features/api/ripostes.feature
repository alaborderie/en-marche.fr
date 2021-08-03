@api
Feature:
  In order to see ripostes
  I should be able to access API of riposte

  Background:
  Given the following fixtures are loaded:
    | LoadClientData    |
    | LoadAdherentData  |
    | LoadPollData      |

  Scenario: As a logged-in user I can retrieve ripostes
  Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
  When I send a "GET" request to "/api/v3/ripostes"
  Then the response status code should be 200
  And the JSON should be equal to:
  """
  """

  Scenario: As a logged-in user I can get a riposte
  Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
  When I send a "GET" request to "/api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4"
  Then the response status code should be 200
  And the JSON should be equal to:
  """
  """

  Scenario: As a logged-in user I cannot retrieve disabled riposte
    Given I send a "GET" request to "/api/v3/ripostes/80b2eb70-38c3-425e-8c1d-a90e84e1a4b3"
    Then the response status code should be 404

  Scenario: As a logged-in user I cannot retrieve riposte created more than 24 hours
    Given I send a "GET" request to "/api/v3/ripostes/5222890b-8cf7-45e3-909a-049f1ba5baa4"
    Then the response status code should be 404

  Scenario: As a non logged-in user I cannot retrieve ripostes
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/ripostes"
    When I send a "GET" request to "/api/v3/ripostes"
    Then the response status code should be 403

  Scenario: As a non logged-in user I cannot retrieve ripostes
    Given I send a "GET" request to "/api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4"
    Then the response status code should be 403
