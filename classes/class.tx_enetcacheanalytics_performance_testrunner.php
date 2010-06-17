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
class tx_enetcacheanalytics_performance_TestRunner {
	/**
	 * @var array Holds gathered test results
	 */
	protected $testResults = array();

	protected static $configuredBackends = array(
		'DbBackend',
//		'MemcachedBackend',
	);

	public function run() {
		foreach (self::$configuredBackends as $backendName) {
			$backend = t3lib_div::makeInstance('tx_enetcacheanalytics_performance_backend_' . $backendName);
			$backend->setUp();
			$this->testResults['setWithSingleTag_100'][$backendName] = $backend->setCacheEntriesWithSingleTag(100);
			$backend->tearDown();
		}
	}

	public function getTestResults() {
		return $this->testResults;
	}
} // end of class
?>
