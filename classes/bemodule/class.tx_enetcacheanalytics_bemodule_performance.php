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
	 * @var tx_enetcacheanalytics_performance_message_list List of unavailable backends
	 */
	protected $unavailableBackends;

	/**
	 * @var tx_enetcacheanalytics_utility_UserData Object to get and store be_user module data uc
	 */
	protected $userData;

	/**
	 * Default init method, required by interface
	 *
	 * @return void
	 */
	public function init(tx_enetcacheanalytics_module1 &$pObj) {
		$this->pObj = $pObj;
		$this->GPvars = $pObj->getGPvars();
		$this->testSuite = t3lib_div::makeInstance('tx_enetcacheanalytics_performance_TestSuite');
		$this->userData = t3lib_div::makeInstance('tx_enetcacheanalytics_utility_UserData');
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

			// Store changed user data in be_user module data
			// @TODO: Move class instance and handling to mod1/index.php class, make use in analytics module as well
			// @TODO: restructure weird array value handling
		$this->userData->persist();
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
				$this->setSelectedTestcases();
				$this->testSuite->run();
				$this->testStatistics = $this->testSuite->getTestResults();
				$this->unavailableBackends = $this->testSuite->getUnavailableBackends();
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
		$content[] = $this->renderTestcaseSelectionSection();
		$content[] = $this->renderMessageTypeSelectionSection();
		$this->renderUnavailableBackendsFlashMessages();
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
	 * Set testcases to run by given GPvars
	 *
	 * @return void
	 */
	protected function setSelectedTestcases() {
		$availableTestcases = $this->testSuite->getTestcases();
		$selectedTestcases = array();
		if (isset($this->GPvars['tx_enetcacheanalytics_testcaseSelectionDone'])) {
			if (is_array($this->GPvars['tx_enetcacheanalytics_testcaseSelection'])) {
				foreach($this->GPvars['tx_enetcacheanalytics_testcaseSelection'] as $backendName => $enabled) {
					if (in_array($backendName, $availableTestcases)) {
						$selectedTestcases[] = $backendName;
					}
				}
			}
			$this->testSuite->setSelectedTestcases($selectedTestcases);
		}
	}

	/**
	 * Fetch available testcases and render checkboxes to choose a subset,
	 * store selection changen in be_user uc
	 *
	 * @return string HTML of testcase selector
	 */
	protected function renderTestcaseSelectionSection() {
		$content = array();
		$availableTestcases = $this->testSuite->getTestcases();
		foreach ($availableTestcases as $backend) {
			$selected = TRUE;
			$userUc = $this->userData['performance_selectedTestcases'];
			if (isset($this->GPvars['tx_enetcacheanalytics_testcaseSelectionDone'])) {
				$selected = $this->GPvars['tx_enetcacheanalytics_testcaseSelection'][$backend] ? TRUE : FALSE;
				$userUc[$backend] = $selected;
			} elseif (isset($userUc[$backend])) {
				$selected = $userUc[$backend];
			}
			$this->userData['performance_selectedTestcases'] = $userUc;
			$content[] = tx_enetcacheanalytics_utility_formhelper::makeCheckbox('testcaseSelection', 1, $selected, $backend);
			$content[] = $backend . '<br />';
		}
		$content[] = tx_enetcacheanalytics_utility_formhelper::makeHiddenField('testcaseSelectionDone', 1);
		return $this->finalizeSection($content, 'Select testcases to run');
	}

	/**
	 * Fetch available backends and render checkboxes to choose
	 * backends to run test suite on, store selection changes in be_user uc
	 *
	 * @return string HTML of backend selector
	 */
	protected function renderBackendSelectionSection() {
		$content = array();
		$availableBackends = $this->testSuite->getBackends();
		foreach ($availableBackends as $backend) {
			$selected = TRUE;
			$userUc = $this->userData['performance_selectedBackends'];
			if (isset($this->GPvars['tx_enetcacheanalytics_backendSelectionDone'])) {
				$selected = $this->GPvars['tx_enetcacheanalytics_backendSelection'][$backend] ? TRUE : FALSE;
				$userUc[$backend] = $selected;
			} elseif (isset($userUc[$backend])) {
				$selected = $userUc[$backend];
			}
			$this->userData['performance_selectedBackends'] = $userUc;
			$content[] = tx_enetcacheanalytics_utility_formhelper::makeCheckbox('backendSelection', 1, $selected, $backend);
			$content[] = $backend . '<br />';
		}
		$content[] = tx_enetcacheanalytics_utility_formhelper::makeHiddenField('backendSelectionDone', 1);
		return $this->finalizeSection($content, 'Select backends to run tests on');
	}

	/**
	 * Fetch available message types and render checkboxes to choose.
	 * Used as a legend for the table display, too
	 * Store selection in be_user module data
	 *
	 * @return string HTML of message type selection
	 */
	protected function renderMessageTypeSelectionSection() {
		$content = array();
		$possibleMessageTypes = tx_enetcacheanalytics_performance_message_List::getPossibleMessageTypes();
		foreach ($possibleMessageTypes as $messageType) {
			$selected = TRUE;
			$userUc = $this->userData['performance_selectedMessages'];
			if (isset($this->GPvars['tx_enetcacheanalytics_messageTypeSelectionDone'])) {
				$selected = $this->GPvars['tx_enetcacheanalytics_messageTypeSelection'][$messageType] ? TRUE : FALSE;
				$userUc[$messageType] = $selected;
			} elseif (isset($userUc[$messageType])) {
				$selected = $userUc[$messageType];
			}
			$this->userData['performance_selectedMessages'] = $userUc;
			$content[] = tx_enetcacheanalytics_utility_formhelper::makeCheckbox('messageTypeSelection', 1, $selected, $messageType);
			$message = t3lib_div::makeInstance('tx_enetcacheanalytics_performance_message_' . $messageType);
			$content[] = '<span class="' . $messageType . '">' . $message['message'] . '</span><br />';
		}
		$content[] = tx_enetcacheanalytics_utility_formhelper::makeHiddenField('messageTypeSelectionDone', 1);
		return $this->finalizeSection($content, 'Select different message types to show');
	}

	/**
	 * Render unavailable backend flash messages after test run
	 *
	 * @return void
	 */
	protected function renderUnavailableBackendsFlashMessages() {
		if (is_object($this->unavailableBackends)) {
			foreach ($this->unavailableBackends as $backend) {
				$this->pObj->addMessage(
					$backend['message'],
					t3lib_FlashMessage::WARNING
				);
			}
		}
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
		$testsuiteRuntime = $this->testSuite->getRuntime();
		return $this->finalizeSection($content, 'Testsuite runtime: ' . $this->formatTimeMessage($testsuiteRuntime['value']));
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
			$headerTR[] = '<td><p class="TimeMessage">' . $this->formatTimeMessage($accumulatedTestTime) . '</p></td>';
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
		$enabledMessageTypes = array_keys($this->GPvars['tx_enetcacheanalytics_messageTypeSelection']);
		foreach ($messageList as $message) {
			$messageType = str_replace('tx_enetcacheanalytics_performance_message_', '', get_class($message));
			$showMessage = in_array($messageType, $enabledMessageTypes) ? TRUE : FALSE;
			if ($showMessage) {
					// Special hack for TimeMessage to crop data at some position after decimal point
				if ($messageType === 'TimeMessage') {
					$value = $this->formatTimeMessage($message['value']);
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

	/**
	 * Format time statistics
	 *
	 * @return string Formatted time
	 */
	protected function formatTimeMessage($value) {
		return sprintf("%.4f", $value);
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
