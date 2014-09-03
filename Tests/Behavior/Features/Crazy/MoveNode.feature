Feature: Move node
  In order to move nodes
  As an API user of the content repository
  I need support to move nodes and child nodes considering workspaces

  Background:
    Given I have the following nodes:
      | Identifier                           | Path                   | Node Type                      | Properties           | Workspace |
      | ecf40ad1-3119-0a43-d02e-55f8b5aa3c70 | /sites                 | unstructured                   |                      | live      |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/typo3cr         | TYPO3.TYPO3CR.Testing:Document | {"title": "Home"}    | live      |
      | 68ca0dcd-2afb-ef0e-1106-a5301e65b8a0 | /sites/typo3cr/company | TYPO3.TYPO3CR.Testing:Document | {"title": "Company"} | live      |
      | 52540602-b417-11e3-9358-14109fd7a2dd | /sites/typo3cr/service | TYPO3.TYPO3CR.Testing:Document | {"title": "Service"} | live      |

  @fixtures
  Scenario: Move a node (into) in user workspace and get by path
    When I get a node by path "/sites/typo3cr/service" with the following context:
      | Workspace  |
      | user-admin |
    And I move the node into the node with path "/sites/typo3cr/company"
    And I get a node by path "/sites/typo3cr/company/service" with the following context:
      | Workspace  |
      | user-admin |
    Then I should have one node
    And I get a node by path "/sites/typo3cr/service" with the following context:
      | Workspace  |
      | user-admin |
    Then I should have 0 nodes

  @fixtures
  Scenario: Move a node (into) in user workspace and get nodes on path
    When I get a node by path "/sites/typo3cr/service" with the following context:
      | Workspace  |
      | user-admin |
    And I move the node into the node with path "/sites/typo3cr/company"
    And I get the nodes on path "/sites/typo3cr" to "/sites/typo3cr/company/service" with the following context:
      | Workspace  |
      | user-admin |
    Then I should have 3 nodes
    And I get the nodes on path "/sites/typo3cr" to "/sites/typo3cr/service" with the following context:
      | Workspace  |
      | user-admin |
    Then I should have one node

  @fixtures
  Scenario: Move a node (into) in user workspace and get child nodes
    When I get a node by path "/sites/typo3cr/service" with the following context:
      | Workspace  |
      | user-admin |
    And I move the node into the node with path "/sites/typo3cr/company"
    And I get the child nodes of "/sites/typo3cr" with filter "TYPO3.TYPO3CR.Testing:Document" and the following context:
      | Workspace  |
      | user-admin |
    Then I should have the following nodes:
      | Path                   |
      | /sites/typo3cr/company |
    And I get the child nodes of "/sites/typo3cr/company" with filter "TYPO3.TYPO3CR.Testing:Document" and the following context:
      | Workspace  |
      | user-admin |
    Then I should have the following nodes:
      | Path                           |
      | /sites/typo3cr/company/service |

  @fixtures
  Scenario: Move a node (into) in user workspace and publish single node
    When I get a node by path "/sites/typo3cr/service" with the following context:
      | Workspace  |
      | user-admin |
    And I move the node into the node with path "/sites/typo3cr/company"
    When I get a node by path "/sites/typo3cr/company/service" with the following context:
      | Workspace  |
      | user-admin |
    And I publish the node
    And I get a node by path "/sites/typo3cr/company/service" with the following context:
      | Workspace |
      | live      |
    Then I should have one node
    And the unpublished node count in workspace "user-admin" should be 0

  @fixtures
  Scenario: Move a node (into) and move it back
    When I get a node by path "/sites/typo3cr/service" with the following context:
      | Workspace  |
      | user-admin |
    And I move the node into the node with path "/sites/typo3cr/company"
    When I get a node by path "/sites/typo3cr/company/service" with the following context:
      | Workspace  |
      | user-admin |
    And I move the node into the node with path "/sites/typo3cr"

    When I get a node by path "/sites/typo3cr/service" with the following context:
      | Workspace  |
      | user-admin |
    Then I should have one node
    # TODO This would be nice, but right now we might not be able to do this reliably
    # And the unpublished node count in workspace "user-admin" should be 0

  @fixtures
  Scenario: Move a node (before) in user workspace and get by path
    Given I have the following nodes:
      | Identifier                           | Path                         | Node Type                      | Properties         | Workspace |
      | a282e974-2dd2-11e4-ae5a-14109fd7a2dd | /sites/typo3cr/company/about | TYPO3.TYPO3CR.Testing:Document | {"title": "About"} | live      |
    When I get a node by path "/sites/typo3cr/service" with the following context:
      | Workspace  |
      | user-admin |
    And I move the node before the node with path "/sites/typo3cr/company/about"
    And I get the child nodes of "/sites/typo3cr/company" with filter "TYPO3.TYPO3CR.Testing:Document" and the following context:
      | Workspace  |
      | user-admin |
    Then I should have the following nodes:
      | Path                           |
      | /sites/typo3cr/company/service |
      | /sites/typo3cr/company/about   |
    And I get a node by path "/sites/typo3cr/company/service" with the following context:
      | Workspace  |
      | user-admin |
    Then I should have one node
    And I get a node by path "/sites/typo3cr/service" with the following context:
      | Workspace  |
      | user-admin |
    Then I should have 0 nodes

  @fixtures
  Scenario: Move a node (after) in user workspace and get by path
    Given I have the following nodes:
      | Identifier                           | Path                         | Node Type                      | Properties         | Workspace |
      | a282e974-2dd2-11e4-ae5a-14109fd7a2dd | /sites/typo3cr/company/about | TYPO3.TYPO3CR.Testing:Document | {"title": "About"} | live      |
    When I get a node by path "/sites/typo3cr/service" with the following context:
      | Workspace  |
      | user-admin |
    And I move the node after the node with path "/sites/typo3cr/company/about"
    And I get the child nodes of "/sites/typo3cr/company" with filter "TYPO3.TYPO3CR.Testing:Document" and the following context:
      | Workspace  |
      | user-admin |
    Then I should have the following nodes:
      | Path                           |
      | /sites/typo3cr/company/about   |
      | /sites/typo3cr/company/service |
    And I get a node by path "/sites/typo3cr/company/service" with the following context:
      | Workspace  |
      | user-admin |
    Then I should have one node
    And I get a node by path "/sites/typo3cr/service" with the following context:
      | Workspace  |
      | user-admin |
    Then I should have 0 nodes