<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010  Christian Kuhn <lolli@schwarzbu.ch>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Class test implementation for db backend
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_performance_backend_DbBackend extends tx_enetcacheanalytics_performance_backend_AbstractBackend {
	/**
	 * Used table names
	 */
	protected static $cacheTable = 'tx_enetcacheanalytics_performance';
	protected static $tagsTable = 'tx_enetcacheanalytics_performance_tags';

	/**
	 * Query counter when test starts
	 */
	protected $queryCountStart;

	/**
	 * Set up this backend
	 */
	public function setUp() {
		$this->createTables();

		$this->backend = t3lib_div::makeInstance(
			't3lib_cache_backend_DbBackend',
			array(
				'cacheTable' => self::$cacheTable,
				'tagsTable' => self::$tagsTable,
			)
		);

		$this->backend->setCache($this->getMockFrontend());
	}

	protected function createTables() {
		$GLOBALS['TYPO3_DB']->sql_query('CREATE TABLE ' . self::$cacheTable . ' (
			id int(11) unsigned NOT NULL auto_increment,
			identifier varchar(128) DEFAULT \'\' NOT NULL,
			crdate int(11) unsigned DEFAULT \'0\' NOT NULL,
			content mediumtext,
			lifetime int(11) unsigned DEFAULT \'0\' NOT NULL,
			PRIMARY KEY (id),
			KEY cache_id (identifier)
		) ENGINE=InnoDB;
		');

		$GLOBALS['TYPO3_DB']->sql_query('CREATE TABLE ' . self::$tagsTable. ' (
			id int(11) unsigned NOT NULL auto_increment,
			identifier varchar(128) DEFAULT \'\' NOT NULL,
			tag varchar(128) DEFAULT \'\' NOT NULL,
			PRIMARY KEY (id),
			KEY cache_id (identifier),
			KEY cache_tag (tag)
		) ENGINE=InnoDB;
		');
	}

	public function tearDown() {
		$GLOBALS['TYPO3_DB']->sql_query(
			'DROP TABLE ' . self::$cacheTable . ';'
		);
		$GLOBALS['TYPO3_DB']->sql_query(
			'DROP TABLE ' . self::$tagsTable . ';'
		);
	}

	public function set($numberOfEntries = 100) {
		$this->queryCountStart();
		$messageList = parent::set($numberOfEntries);
		$messageList->add($this->getQueryCountMessage());
		return $messageList;
	}

	public function setSingleTag($numberOfEntries = 100) {
		$this->queryCountStart();
		$messageList = parent::setSingleTag($numberOfEntries);
		$messageList->add($this->getQueryCountMessage());
		return $messageList;
	}

	public function setKiloBytesOfData($dataSizeInKB = 100) {
		$this->queryCountStart();
		$messageList = parent::setKiloBytesOfData($dataSizeInKB);
		$messageList->add($this->getQueryCountMessage());
		return $messageList;
	}

	public function setMultipleTags($numberOfTags = 100) {
		$this->queryCountStart();
		$messageList = parent::setMultipleTags($numberOfTags);
		$messageList->add($this->getQueryCountMessage());
		return $messageList;
	}

	public function dropMultipleTags($numberOfTags = 100) {
		$this->queryCountStart();
		$messageList = parent::dropMultipleTags($numberOfTags);
		$messageList->add($this->getQueryCountMessage());
		return $messageList;
	}

	public function get($numberOfEntries = 100) {
		$this->queryCountStart();
		$messageList = parent::get($numberOfEntries);
		$messageList->add($this->getQueryCountMessage());
		return $messageList;
	}

	public function dropBySingleTag($numberOfEntries = 100) {
		$this->queryCountStart();
		$messageList = parent::dropBySingleTag($numberOfEntries);
		$messageList->add($this->getQueryCountMessage());
		return $messageList;
	}

	public function flush() {
		$this->queryCountStart();
		$messageList = parent::flush();
		$messageList->add($this->getQueryCountMessage());
		return $messageList;
	}

	protected function getQueryCountMessage() {
		$message = t3lib_div::makeInstance('tx_enetcacheanalytics_performance_message_OperationCountMessage');
		$message['value'] = $this->getNumberOfQueriesPerformed();
		return $message;
	}
	protected function queryCountStart() {
		$this->queryCountStart = $this->getQueryCounter();
	}
	protected function getNumberOfQueriesPerformed() {
		return ($this->getQueryCounter() - $this->queryCountStart - 1);
	}
	protected function getQueryCounter() {
		$res = $GLOBALS['TYPO3_DB']->admin_query('SHOW STATUS;');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($row['Variable_name'] == 'Questions') {
				$queryCount = $row['Value'];
				break;
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $queryCount;
	}
}
?>
