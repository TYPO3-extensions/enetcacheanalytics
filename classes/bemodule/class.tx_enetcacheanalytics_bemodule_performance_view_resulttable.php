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
 * Class 'Performance table result' for the 'enetcacheanalytics' BE module.
 * Render a table from given test results of performance module
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_bemodule_performance_view_ResultTable {
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
		$content[] = $this->renderStatisticsTable();
		return(implode(chr(10), $content));
	}

	/**
	 * @return string HTML of statistic table
	 */
	protected function renderStatisticsTable() {
		$content = array();
		$content[] = '<table>';
		$content[] = $this->renderStatisticsTableHeader();
		$content[] = $this->renderStatisticsTimeTakenForTestsHeaderRow();
		$content[] = $this->renderStatisticsTableRows();
		$content[] = '</table>';
		return implode(chr(10), $content);
	}

	/**
	 * @return string HTML of statistics table header
	 */
	protected function renderStatisticsTableHeader() {
		$headerTH = array();
		$headerTH[] = '<th style="width: 20px;"></th>';
		$headerTH[] = '<th>Run</th>';
		foreach ($this->testStatistics as $backendName => $value) {
			$headerTH[] = '<th>' . $backendName . '</th>';
		}

		$content = array();
		$content[] = '<tr class="head">';
		$content[] = implode(chr(10), $headerTH);
		$content[] = '</tr>';

		return implode(chr(10), $content);
	}

	/**
	 * @return string HTML of header row of accumulated time taken for each backend
	 */
	protected function renderStatisticsTimeTakenForTestsHeaderRow() {
		$headerTR = array();
		$headerTR[] = '<td colspan="2">Test time</td>';
		foreach ($this->testStatistics as $testRun) {
			$accumulatedTestTime = 0;
			foreach ($testRun as $testStats) {
				foreach ($testStats as $messageList) {
					foreach ($messageList as $message) {
						if ($message instanceof tx_enetcacheanalytics_performance_message_TimeMessage) {
							$accumulatedTestTime = $accumulatedTestTime + $message['value'];
						}
					}
				}
			}
			$headerTR[] = '<td><p class="TimeMessage">' . $this->pObj->formatTimeMessage($accumulatedTestTime) . '</p></td>';
		}
		$content = array();
		$content[] = '<tr class="user">';
		$content[] = implode(chr(10), $headerTR);
		$content[] = '</tr>';
		return implode(chr(10), $content);
	}

	/**
	 * @return string HTML of statistics table rows
	 */
	protected function renderStatisticsTableRows() {
		$content = array();

		$backendNames = array_keys($this->testStatistics);
		$aBackendName = current($backendNames);
		$testNames = array_keys(current($this->testStatistics));

		foreach ($testNames as $testName) {
			$content[] = $this->renderStatisticsTableTestNameRow($testName);
			$testcaseRunIdentifiers = array_keys($this->testStatistics[$aBackendName][$testName]);
			foreach ($testcaseRunIdentifiers as $testcaseRunIdentifier) {
				$content[] = '<tr class="success">';
				$content[] = '<td style="visibility: hidden;"></td>';
				$content[] = '<td>' . $testcaseRunIdentifier . '</td>';
				foreach ($backendNames as $backendName) {
					$content[] = $this->renderStatisticsTableDetail($this->testStatistics[$backendName][$testName][$testcaseRunIdentifier]);
				}
				$content[] = '</tr>';
			}
		}

		return implode(chr(10), $content);
	}

	/**
	 * @return string HTML statistics table row with test name
	 */
	protected function renderStatisticsTableTestNameRow($testName) {
		$content = array();
		$content[] = '<tr class="user">';
		$colSpanCount = count($this->testStatistics) + 2;
		$content[] = '<td colspan="' . $colSpanCount . '">Test: ' . $testName . '</td>';
		$content[] = '</tr>';
		return implode(chr(10), $content);
	}

	/**
	 * Gets list of messages for one test (one table cell),
	 * check if it should be rendered and build HTML foo if so
	 *
	 * @return string HTML detail part
	 */
	protected function renderStatisticsTableDetail($messageList) {
		$content = array();
		$content[] = '<td>';
		$row = array();
		$enabledMessageTypes = t3lib_div::makeInstance('tx_enetcacheanalytics_utility_UserData')->getEnabledMessages();
		foreach ($messageList as $message) {
			$messageType = str_replace('tx_enetcacheanalytics_performance_message_', '', get_class($message));
			$showMessage = in_array($messageType, $enabledMessageTypes) ? TRUE : FALSE;
			if ($showMessage) {
					// Special hack for TimeMessage to crop data at some position after decimal point
				if ($messageType === 'TimeMessage') {
					$value = $this->pObj->formatTimeMessage($message['value']);
				} else {
					$value = $message['value'];
				}
				$row[] = '<p class="' . $messageType . '">' . $value . '</p>';
			}
		}
		$content[] = implode(chr(10), $row);
		$content[] = '</td>';
		return implode(chr(10), $content);
	}
} // end of class
?>
