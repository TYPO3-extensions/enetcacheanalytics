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
 * Class test implementation for memcache backend
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_performance_backend_MemcachedBackend extends tx_enetcacheanalytics_performance_backend_AbstractBackend {
	/**
	 * Memcached Daemon server
	 */
	const memcachedHost = 'localhost';
	const memcachedPort = '11211';

	/**
	 * Memcache operation counter when test start
	 */
	protected $setCountStart;
	protected $getCountStart;

	/**
	 * Set up this backend
	 */
	public function setUp() {
		$this->testMemcacheAvailable();

		$this->backend = t3lib_div::makeInstance(
			't3lib_cache_backend_MemcachedBackend',
			array(
				'servers' => array(self::memcachedHost . ':' . self::memcachedPort),
			)
		);

		$this->backend->setCache($this->getMockFrontend());
	}

	protected function testMemcacheAvailable() {
		if (!extension_loaded('memcache')) {
			throw t3lib_div::makeInstance('tx_enetcacheanalytics_exception_UnavailableBackend',
				'memcache extension was not available',
				1277127700
			);
		}
		try {
			if (!@fsockopen(self::memcachedHost, self::memcachedPort)) {
				throw t3lib_div::makeInstance('tx_enetcacheanalytics_exception_UnavailableBackend',
					'memcache sever not available',
					1277127722
				);
			}
		} catch (Exception $e) {
			throw t3lib_div::makeInstance('tx_enetcacheanalytics_exception_UnavailableBackend',
				'memcache sever not available',
				1277127734
			);
		}
	}

	public function tearDown() {
		$this->backend->flush();
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
		$stats = $this->getMemcachedStats();
		$this->setCountStart = $stats['cmd_set'];
		$this->getCountStart = $stats['cmd_get'];
	}
	protected function getNumberOfQueriesPerformed() {
		$stats = $this->getMemcachedStats();
		$setOperations = $stats['cmd_set'] - $this->setCountStart;
		$getOperations = $stats['cmd_get'] - $this->getCountStart;
		return ($setOperations + $getOperations);
	}
	protected function getMemcachedStats() {
		$memcache = t3lib_div::makeInstance('Memcache');
		$memcache->addserver(self::memcachedHost, self::memcachedPort);
		$stats = $memcache->getStats();
		unset($memcache);
		return $stats;
	}
}
?>
