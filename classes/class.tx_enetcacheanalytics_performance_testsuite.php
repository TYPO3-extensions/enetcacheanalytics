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
 * Class 'TestRunner'
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_performance_TestSuite {
	/**
	 * @var array Holds gathered test results
	 */
	protected $testResults = array();

	/**
	 * @var array All available backends
	 */
	protected static $backends = array(
		'ApcBackend',
		'DbBackend',
		'DbBackendCompressed',
		'FileBackend',
		'MemcachedBackend',
		'MemcachedBackendCompressed',
		'PdoBackendSqlite',
	);

	/**
	 * @var array Selected backends to run tests on
	 */
	protected $selectedBackends = array();

	/**
	 * Default constructor
	 */
	public function __construct() {
		$this->selectedBackends = self::$backends;
	}

	/**
	 * Execute test suite
	 *
	 * @return void
	 */
	public function run() {
		foreach ($this->selectedBackends as $backendName) {
			$backend = t3lib_div::makeInstance('tx_enetcacheanalytics_performance_backend_' . $backendName);

			try {
					// setUp should throw if backend is not available for some reason
				$backend->setUp();
				$this->runTests($backendName, $backend);
				$backend->tearDown();
			} catch (Exception $e) {
			}

			unset($backend);
			sleep(1);
		}
	}

	/**
	 * Test definitions and test order
	 *
	 * @param string Backend name
	 * @param tx_enetcacheanalytics_performance_backend_AbstractBackend Backend instance
	 * @return void
	 */
	protected function runTests($backendName, tx_enetcacheanalytics_performance_backend_AbstractBackend $backend) {
		$this->testResults['setWithSingleTag_100_1'][$backendName] = $backend->setCacheEntriesWithSingleTag(100);
		$this->testResults['setWithSingleTag_100_2'][$backendName] = $backend->setCacheEntriesWithSingleTag(100);
		$this->testResults['setWithSingleTag_100_3'][$backendName] = $backend->setCacheEntriesWithSingleTag(100);
		$this->testResults['setWithSingleTag_400'][$backendName] = $backend->setCacheEntriesWithSingleTag(400);
		$this->testResults['setWithSingleTag_1600'][$backendName] = $backend->setCacheEntriesWithSingleTag(1600);

		$this->testResults['getPeviouslySetEntriesWithSingleTag_100_1'][$backendName] = $backend->getCacheEntriesWithSingleTagByIdentifier(100);
		$this->testResults['getPeviouslySetEntriesWithSingleTag_100_2'][$backendName] = $backend->getCacheEntriesWithSingleTagByIdentifier(100);
		$this->testResults['getPeviouslySetEntriesWithSingleTag_100_3'][$backendName] = $backend->getCacheEntriesWithSingleTagByIdentifier(100);
		$this->testResults['getPeviouslySetEntriesWithSingleTag_400'][$backendName] = $backend->getCacheEntriesWithSingleTagByIdentifier(400);
		$this->testResults['getPeviouslySetEntriesWithSingleTag_1600'][$backendName] = $backend->getCacheEntriesWithSingleTagByIdentifier(1600);

		$this->testResults['dropPeviouslySetEntriesWithSingleTag_100'][$backendName] = $backend->dropCacheEntriesBySingleTag(100);
		$this->testResults['dropPeviouslySetEntriesWithSingleTag_400'][$backendName] = $backend->dropCacheEntriesBySingleTag(400);
		$this->testResults['dropPeviouslySetEntriesWithSingleTag_1600'][$backendName] = $backend->dropCacheEntriesBySingleTag(1600);

		$this->testResults['setWithKiloBytesOfData_100'][$backendName] = $backend->setWithKiloBytesOfData(100);
		$this->testResults['setWithKiloBytesOfData_400'][$backendName] = $backend->setWithKiloBytesOfData(400);
		$this->testResults['setWithKiloBytesOfData_1600'][$backendName] = $backend->setWithKiloBytesOfData(1600);
	}

	/**
	 * Return available backends
	 *
	 * @return array backend names
	 */
	public function getBackends() {
		return self::$backends;
	}

	/**
	 * Set available backends
	 *
	 * @param array Backends
	 * @return void
	 */
	public function setSelectedBackends(array $backends = array()) {
		$this->selectedBackends = $backends;
	}

	/**
	 * Get accumulated test results
	 *
	 * @return array Test messages
	 */
	public function getTestResults() {
		return $this->testResults;
	}
} // end of class
?>
