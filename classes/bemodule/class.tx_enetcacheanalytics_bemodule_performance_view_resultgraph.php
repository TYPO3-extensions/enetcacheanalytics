<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Christian Kuhn <lolli@schwarzbu.ch>
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
 * Class 'Performance graph result' for the 'enetcacheanalytics' BE module.
 * Render graphs fropm given test results of performance module
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_bemodule_performance_view_ResultGraph {
	/**
	 * @var tx_enetcacheanalytics_bemodule Default parent object
	 */
	protected $pObj = object;

	/**
	 * @var array Gathered output of performance tests
	 */
	protected $testStatistics = array();

	/**
	 * Default constructor
	 */
	public function __construct($pObj) {
		$this->pObj = $pObj;
		$this->testStatistics = $pObj->getTestResults();
	}

	/**
	 * Render result table
	 *
	 * @return string HTML
	 */
	public function render() {
		$content = array();
		$content[] = $this->renderGraphs();
		return(implode(chr(10), $content));
	}

	protected function renderGraphs() {
		$chartsDataArray = $this->getChartsDataArray();
		return $content;
	}

	protected function getChartsDataArray() {
		$backendNames = array_keys($this->testStatistics);
		$aBackendName = current($backendNames);
		$testNames = array_keys(current($this->testStatistics));

			// Initialize data array
		$chartsDataArray = array();
		foreach ($testNames as $testName) {
			$chartsDataArray[] = array(
				'title' => $testName,
				'x' => array_keys($this->testStatistics[$aBackendName][$testName]),
				'y' => $backendNames,
				'labels' => $backendNames
			);
		}

			// Now calculate prime numbers :)
		$backendCounter = 0;
		foreach ($this->testStatistics as $backendTests) {
			$testcaseCounter = 0;
			foreach ($backendTests as $testcase) {
				$chartsDataArray[$testcaseCounter]['y'][$backendCounter] = array();
				foreach ($testcase as $testrunMessageList) {
					foreach ($testrunMessageList as $message) {
						if ($message instanceof tx_enetcacheanalytics_performance_message_TimeMessage) {
							$chartsDataArray[$testcaseCounter]['y'][$backendCounter][] = $this->pObj->formatTimeMessage($message['value']);
						}
					}
				}
				$testcaseCounter ++;
			}
			$backendCounter ++;
		}

		return $chartsDataArray;
	}
} // end of class
?>
