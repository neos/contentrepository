<?php
declare(ENCODING = 'utf-8');

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @package TYPO3CR
 * @version $Id:F3_TYPO3CR_Storage_Backend_PDO.php 888 2008-05-30 16:00:05Z k-fish $
 */

require_once('Zend/Search/Lucene.php');

/**
 * A helper class for the storage layer
 *
 * @package TYPO3CR
 * @version $Id:F3_TYPO3CR_Storage_Backend_PDO.php 888 2008-05-30 16:00:05Z k-fish $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class F3_TYPO3CR_Storage_Helper {

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var PDO
	 */
	protected $databaseHandle;

	/**
	 * @param array $options
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function __construct($options) {
		$this->options = $options;
	}

	/**
	 * Performs all-in-one setup of the TYPO3CR storage layer
	 *
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function initialize() {
		$this->initializeStorage();
		$this->initializeSearch();
	}

	/**
	 * Sets up tables, nodetypes and root node
	 *
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @author Matthias Hoermann <hoermann@saltation.de>
	 */
	public function initializeStorage() {
		$this->databaseHandle = new PDO($this->options['dsn'], $this->options['userid'], $this->options['password']);
		$this->databaseHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$this->initializeTables();
		$this->initializeNamespaces();
		$this->initializeNodeTypes();
		$this->initializeNodes();
	}

	/**
	 * Creates the tables needed
	 *
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function initializeTables() {
		$statements = file(FLOW3_PATH_PACKAGES . 'TYPO3CR/Resources/SQL/TYPO3CR.sql', FILE_IGNORE_NEW_LINES & FILE_SKIP_EMPTY_LINES);
		foreach ($statements as $statement) {
			$this->databaseHandle->query($statement);
		}
	}

	/**
	 * Clears the namespaces table and adds builtin namespaces to the database
	 *
	 * @return void
	 * @author Matthias Hoermann <hoermann@saltation.de>
	 */
	public function initializeNamespaces() {
		$this->databaseHandle->query('DELETE FROM "namespaces"');
		$this->databaseHandle->query('INSERT INTO "namespaces" ("prefix", "uri") VALUES (\'jcr\', \'http://www.jcp.org/jcr/1.0\')');
		$this->databaseHandle->query('INSERT INTO "namespaces" ("prefix", "uri") VALUES (\'nt\', \'http://www.jcp.org/jcr/nt/1.0\')');
		$this->databaseHandle->query('INSERT INTO "namespaces" ("prefix", "uri") VALUES (\'mix\', \'http://www.jcp.org/jcr/mix/1.0\')');
		$this->databaseHandle->query('INSERT INTO "namespaces" ("prefix", "uri") VALUES (\'xml\', \'http://www.w3.org/XML/1998/namespace\')');
		$this->databaseHandle->query('INSERT INTO "namespaces" ("prefix", "uri") VALUES (\'flow3\', \'http://forge.typo3.org/namespaces/flow3\')');
	}

	/**
	 * Clears nodetypes and adds builtin nodetypes to the database
	 *
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @author Matthias Hoermann <hoermann@saltation.de>
	 */
	protected function initializeNodeTypes() {
		$this->databaseHandle->query('DELETE FROM "nodetypes"');
		$this->databaseHandle->query('INSERT INTO "nodetypes" ("name","namespace") VALUES (\'base\',\'http://www.jcp.org/jcr/nt/1.0\')');
		$this->databaseHandle->query('INSERT INTO "nodetypes" ("name","namespace") VALUES (\'unstructured\',\'http://www.jcp.org/jcr/nt/1.0\')');
	}

	/**
	 * Clears the nodes table and adds a root node to the database
	 *
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @author Matthias Hoermann <hoermann@saltation.de>
	 */
	protected function initializeNodes() {
		$this->databaseHandle->query('DELETE FROM "nodes"');
		$statementHandle = $this->databaseHandle->prepare('INSERT INTO "nodes" ("identifier", "name", "namespace", "parent", "nodetype", "nodetypenamespace") VALUES (?, \'\', \'\', \'\', \'unstructured\',\'http://www.jcp.org/jcr/nt/1.0\')');
		$statementHandle->execute(array(
			F3_FLOW3_Utility_Algorithms::generateUUID()
		));
	}

	/**
	 * Sets up the search backend
	 *
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function initializeSearch() {
		$index = Zend_Search_Lucene::create($this->options['indexlocation']. '/default');
		$this->populateIndex();
	}

	/**
	 * Adds the root node to the index
	 *
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function populateIndex() {
		$statementHandle = $this->databaseHandle->query('SELECT * FROM "nodes" WHERE "parent" = \'\'');
		$node = $statementHandle->fetch(PDO::FETCH_ASSOC);

		$nodeDocument = new Zend_Search_Lucene_Document();
		$nodeDocument->addField(Zend_Search_Lucene_Field::Keyword('identifier', $node['identifier']));
		$nodeDocument->addField(Zend_Search_Lucene_Field::Keyword('nodetype', $node['nodetype']));
		$nodeDocument->addField(Zend_Search_Lucene_Field::Keyword('path', '/'));

		$index = Zend_Search_Lucene::open($this->options['indexlocation']. '/default');
		$index->addDocument($nodeDocument);
	}
}

?>