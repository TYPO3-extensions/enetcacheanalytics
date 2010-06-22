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
	 * @var array All available test cases
	 */
	protected static $testcases = array(
		'SetMultipleTimes',
		'GetMultipleTimes',
		'SetSingleTag',
		'GetByIdentifier',
		'DropBySingleTag',
		'SetKiloBytesOfData',
		'GetKiloBytesOfData',
		'SetMultipleTags',
		'DropMultipleTags',
		'FlushSingleTag',
		'FlushMultipleTags',
	);

	/**
	 * @var array Selected backends to run tests on
	 */
	protected $selectedBackends = array();

	/**
	 * @var array Selected testcases to run
	 */
	protected $selectedTestcases = array();

	/**
	 * Default constructor sets selected backends to all available backends
	 */
	public function __construct() {
		$this->selectedBackends = self::$backends;
		$this->selectedTestcases = self::$testcases;
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
					// @TODO: Implement better exception handling (must not catch cache backend exceptions!)
//				throw $e;
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

		foreach ($this->selectedTestcases as $testcase) {
			$testcaseInstance = t3lib_div::makeInstance('tx_enetcacheanalytics_performance_testcase_' . $testcase);
			$testcaseInstance->setUp($backend);
			$testcaseName = $testcaseInstance->getName();
			$this->testResults[$backendName][$testcaseName] = $testcaseInstance->run();
			$testcaseInstance->tearDown();
		}
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
	 * Return available testcases
	 *
	 * @return array testcase names
	 */
	public function getTestcases() {
		return self::$testcases;
	}

	/**
	 * Set backends to run tests on
	 *
	 * @param array Backends
	 * @return void
	 */
	public function setSelectedBackends(array $backends = array()) {
		$this->selectedBackends = $backends;
	}

	/**
	 * Set testcases to run
	 *
	 * @param array Testcases
	 * @return void
	 */
	public function setSelectedTestcases(array $testcases = array()) {
		$this->selectedTestcases = $testcases;
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
