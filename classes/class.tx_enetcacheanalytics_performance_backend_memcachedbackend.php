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
			throw new Exception('memcache extension was not available');
		}
		try {
			if (!@fsockopen(self::memcachedHost, self::memcachedPort)) {
				throw new Exception('memcache server not available');
			}
		} catch (Exception $e) {
			throw new Exception('memcache server not available');
		}
	}

	public function tearDown() {
		$this->backend->flush();
	}

	public function setCacheEntriesWithSingleTag($numberOfEntries = 100) {
		$this->queryCountStart();
		$message = parent::setCacheEntriesWithSingleTag($numberOfEntries);
		$message[] = $this->getQueryCountMessage();
		return $message;
	}

	public function getCacheEntriesWithSingleTagByIdentifier($numberOfEntries = 100) {
		$this->queryCountStart();
		$message = parent::getCacheEntriesWithSingleTagByIdentifier($numberOfEntries);
		$message[] = $this->getQueryCountMessage();
		return $message;
	}

	public function dropCacheEntriesBySingleTag($numberOfEntries = 100) {
		$this->queryCountStart();
		$message = parent::dropCacheEntriesBySingleTag($numberOfEntries);
		$message[] = $this->getQueryCountMessage();
		return $message;
	}

	protected function getQueryCountMessage() {
		$message = array(
			'type' => self::INFO,
			'value' => $this->getNumberOfQueriesPerformed(),
			'message' => 'Queries performed',
		);
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
