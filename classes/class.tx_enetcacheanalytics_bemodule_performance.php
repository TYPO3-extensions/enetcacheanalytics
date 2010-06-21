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
 * Class 'Performance' for the 'enetcacheanalytics' BE module.
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_bemodule_performance implements tx_enetcacheanalytics_bemodule {
	/**
	 * @var tx_enetcacheanalytics_module1 Default parent object
	 */
	protected $pObj = object;

	/**
	 * @var array Global get/post vars
	*/
	protected $GPvars = array();

	/**
	 * @var tx_enetcacheanalytics_performance_TestSuite Instance of test suite
	 */
	protected $testSuite;

	/**
	 * @var array Gathered output of performance tests
	 */
	protected $testStatistics = array();

	/**
	 * @var integer Helper value to render HTML table view of statistics
	 */
	protected $statisticsTdCount = 0;

	/**
	 * Default init method, required by interface
	 *
	 * @return void
	 */
	public function init(tx_enetcacheanalytics_module1 &$pObj) {
		$this->pObj = $pObj;
		$this->GPvars = $pObj->getGPvars();
		$this->testSuite = t3lib_div::makeInstance('tx_enetcacheanalytics_performance_TestSuite');
	}

	/**
	 * Default execute, required by interface
	 *
	 * @return void
	 */
	public function execute() {
			// Handle actions triggered by GPvars
		$this->handleAction();

			// Add additional JS to parent object
		$this->pObj->setAdditionalJavascript($this->additionalJavascript());

			// Main content
		$this->pObj->setContentMarker($this->renderMainModuleContent());

			// Additinal functions in doc header
		$this->pObj->setAdditionalDocHeaderMarker($this->renderDocHeaderOptions());
	}

	/**
	 * Handle module specific actions
	 *
	 * @return void
	 */
	protected function handleAction() {
		if (!isset($this->GPvars['tx_enetcacheanalytics_action'])) {
			return;
		}
		switch ($this->GPvars['tx_enetcacheanalytics_action']) {
			case 'performTests':
				$this->setSelectedBackends();
				$this->testSuite->run();
				$this->testStatistics = $this->testSuite->getTestResults();
			break;
		}
	}

	/**
	 * Add additional Javascript to document
	 *
	 * @return string Additional JS
	 */
	protected function additionalJavascript() {
		$javascript = array();
		return implode(chr(10), $javascript);
	}

	/**
	 * Render additional drop downs and actions in document header
	 *
	 * @return string select box HTML
	 */
	protected function renderDocHeaderOptions() {
		$content = array();
		$content[] = tx_enetcacheanalytics_utility_formhelper::makeSubmitButton('Run performance tests', 'performTests');
		return (implode(chr(10), $content));
	}

	/**
	 * @return string HTML of main module
	 */
	protected function renderMainModuleContent() {
		$content = array();
		$content[] = $this->renderBackendSelectionSection();
		if (count($this->testStatistics)) {
			$content[] = $this->renderStatisticsTable();
		}
		return(implode(chr(10), $content));
	}

	/**
	 * Set backends to test by given GPvars
	 *
	 * @return void
	 */
	protected function setSelectedBackends() {
		$availableBackends = $this->testSuite->getBackends();
		$selectedBackends = array();
		if (isset($this->GPvars['tx_enetcacheanalytics_backendSelectionDone'])) {
			if (is_array($this->GPvars['tx_enetcacheanalytics_backendSelection'])) {
				foreach($this->GPvars['tx_enetcacheanalytics_backendSelection'] as $backendName => $enabled) {
					if (in_array($backendName, $availableBackends)) {
						$selectedBackends[] = $backendName;
					}
				}
			}
			$this->testSuite->setSelectedBackends($selectedBackends);
		}
	}

	/**
	 * Fetch available backends and render checkboxe to choose
	 * backends to run test suite on
	 *
	 * @return string HTML of backend selector
	 */
	protected function renderBackendSelectionSection() {
		$content = array();
		$availableBackends = $this->testSuite->getBackends();
		foreach ($availableBackends as $backend) {
			$selected = TRUE;
			if (isset($this->GPvars['tx_enetcacheanalytics_backendSelectionDone'])) {
				$selected = $this->GPvars['tx_enetcacheanalytics_backendSelection'][$backend] ? TRUE : FALSE;
			}
			$content[] = tx_enetcacheanalytics_utility_formhelper::makeCheckbox('backendSelection', 1, $selected, $backend);
			$content[] = $backend . '<br />';
		}
		$content[] = tx_enetcacheanalytics_utility_formhelper::makeHiddenField('backendSelectionDone', 1);
		return $this->finalizeSection($content, 'Select backends to run tests on');
	}

	/**
	 * @return string HTML of statistic table
	 */
	protected function renderStatisticsTable() {
		$content = array();
		$content[] = '<table>';
		$content[] = $this->renderStatisticsTableHeader();
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
		$headerTH[] = '<th>Type</th>';
		$this->statisticsTdCount = 2;
		$aStatEntry = next($this->testStatistics);
		foreach ($aStatEntry as $backendName => $value) {
			$headerTH[] = '<th>' . $backendName . '</th>';
			$this->statisticsTdCount ++;
		}

		$content = array();
		$content[] = '<tr class="head">';
		$content[] = implode(chr(10), $headerTH);
		$content[] = '</tr>';

		return implode(chr(10), $content);
	}

	/**
	 * @return string HTML of statistics table rows
	 */
	protected function renderStatisticsTableRows() {
		$content = array();

		foreach ($this->testStatistics as $testName => $testStats) {
			$content[] = $this->renderStatisticsTableTestNameRow($testName);
			$backendNames = array_keys($testStats);
			$numberOfStatisticsDetails = count($testStats[$backendNames[0]]);
			for ($i = 0; $i < $numberOfStatisticsDetails; $i ++) {
				$type = $testStats[$backendNames[0]][$i]['type'];
				if ($type == 0) {
					$class = 'success';
				} else {
					$class = 'set';
				}
				$content[] = '<tr class="' . $class . '">';
				$content[] = '<td style="visibility: hidden;"></td>';
				$content[] = '<td>' . $testStats[$backendNames[0]][$i]['message'] . '</td>';
				foreach ($backendNames as $backendName) {
					$content[] = $this->renderStatisticsTableDetail($testStats[$backendName][$i]);
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
		$content[] = '<td colspan="' . $this->statisticsTdCount . '">Test: ' . $testName . '</td>';
		$content[] = '</tr>';
		return implode(chr(10), $content);
	}

	/**
	 * @return string HTML detail part
	 */
	protected function renderStatisticsTableDetail($detailInformation) {
		$content = array();
		$content[] = '<td>' . $detailInformation['value'] . '</td>';
		return implode(chr(10), $content);
	}

	/**
	 * Wrap content in section and add divider
	 *
	 * @param array content array to be wrapped
	 * @param string name of section
	 * @return string HTML of section
	 */
	protected function finalizeSection($contentArray, $sectionName = '') {
		$content = $this->pObj->getSection($sectionName, implode(chr(10), $contentArray), 0, 1);
		$content .= $this->pObj->doc->divider(5);
		return $content;
	}
} // end of class
?>
