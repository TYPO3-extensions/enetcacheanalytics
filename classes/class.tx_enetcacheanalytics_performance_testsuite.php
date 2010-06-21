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
	 * Default constructor sets selected backends to all available backends
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
				$this->runTests($backend);
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
	 * @param tx_enetcacheanalytics_performance_backend_Backend Backend instance
	 * @return void
	 */
	protected function runTests(tx_enetcacheanalytics_performance_backend_Backend $backend) {
		$backendName = $backend->getName();
		$this->testResults[$backendName] = array();
		$this->testResults[$backendName]['setEntriesMultipleTimes'] = array();
		$this->testResults[$backendName]['setEntriesMultipleTimes']['1'] = $backend->setCacheEntriesWithSingleTag(100);
		$this->testResults[$backendName]['setEntriesMultipleTimes']['2'] = $backend->setCacheEntriesWithSingleTag(100);
		$this->testResults[$backendName]['setEntriesMultipleTimes']['3'] = $backend->setCacheEntriesWithSingleTag(100);

		$this->testResults[$backendName]['setWithSingleTag'] = array();
		$this->testResults[$backendName]['setWithSingleTag']['100'] = $backend->setCacheEntriesWithSingleTag(100);
		$this->testResults[$backendName]['setWithSingleTag']['400'] = $backend->setCacheEntriesWithSingleTag(400);
		$this->testResults[$backendName]['setWithSingleTag']['1600'] = $backend->setCacheEntriesWithSingleTag(1600);

		$this->testResults[$backendName]['getPeviouslySetEntriesMultipleTimes'] = array();
		$this->testResults[$backendName]['getPeviouslySetEntriesMultipleTimes']['1'] = $backend->getCacheEntriesWithSingleTagByIdentifier(100);
		$this->testResults[$backendName]['getPeviouslySetEntriesMultipleTimes']['2'] = $backend->getCacheEntriesWithSingleTagByIdentifier(100);
		$this->testResults[$backendName]['getPeviouslySetEntriesMultipleTimes']['3'] = $backend->getCacheEntriesWithSingleTagByIdentifier(100);

		$this->testResults[$backendName]['getPeviouslySetEntriesWithSingleTag'] = array();
		$this->testResults[$backendName]['getPeviouslySetEntriesWithSingleTag']['100'] = $backend->getCacheEntriesWithSingleTagByIdentifier(100);
		$this->testResults[$backendName]['getPeviouslySetEntriesWithSingleTag']['400'] = $backend->getCacheEntriesWithSingleTagByIdentifier(400);
		$this->testResults[$backendName]['getPeviouslySetEntriesWithSingleTag']['1600'] = $backend->getCacheEntriesWithSingleTagByIdentifier(1600);

		$this->testResults[$backendName]['dropPeviouslySetEntriesWithSingleTag'] = array();
		$this->testResults[$backendName]['dropPeviouslySetEntriesWithSingleTag']['100'] = $backend->dropCacheEntriesBySingleTag(100);
		$this->testResults[$backendName]['dropPeviouslySetEntriesWithSingleTag']['400'] = $backend->dropCacheEntriesBySingleTag(400);
		$this->testResults[$backendName]['dropPeviouslySetEntriesWithSingleTag']['1600'] = $backend->dropCacheEntriesBySingleTag(1600);

		$this->testResults[$backendName]['setWithKiloBytesOfData'] = array();
		$this->testResults[$backendName]['setWithKiloBytesOfData']['100'] = $backend->setWithKiloBytesOfData(100);
		$this->testResults[$backendName]['setWithKiloBytesOfData']['400'] = $backend->setWithKiloBytesOfData(400);
		$this->testResults[$backendName]['setWithKiloBytesOfData']['1600'] = $backend->setWithKiloBytesOfData(1600);
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
