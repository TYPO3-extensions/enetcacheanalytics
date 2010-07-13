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
	}

	/**
	 * Return test results, needed for table / graph view
	 *
	 * @return array test results
	 */
	public function getTestResults() {
		return $this->testSuite->getTestResults();
	}

	/**
	 * Handle module specific actions
	 *
	 * @return void
	 */
	protected function handleAction() {
		$this->intitializeTestSuiteOptions();
		if (!isset($this->GPvars['tx_enetcacheanalytics_action'])) {
			return;
		}
		switch ($this->GPvars['tx_enetcacheanalytics_action']) {
			case 'fold':
				$this->setSectionCollapsedStatus();
			break;
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
	 * Load additional javascript files
	 *
	 * @param array Javascript files to include, relative to extension base path
	 * @return void
	 */
	public function setAdditionalJavascriptFiles(array $files = array()) {
		$this->pObj->setAdditionalJavascriptFiles($files);
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
		$content[] = $this->renderTestSuiteOptionsSelectionSection();
		$content[] = $this->renderBackendSelectionSection();
		$content[] = $this->renderTestcaseSelectionSection();
		$content[] = $this->renderMessageTypeSelectionSection();
		$content[] = $this->renderStatisticsRendererSelectionSection();
		$this->renderUnavailableBackendsFlashMessages();
		if (count($this->testStatistics)) {
			$content[] = $this->renderStatistics();
		}
		return(implode(chr(10), $content));
	}

	/**
	 * Set userdata options of collapsed section based on given form input
	 *
	 * @return void
	 */
	protected function setSectionCollapsedStatus() {
		$this->userData['performance_collapsed' . $this->GPvars['tx_enetcacheanalytics_foldType']] = $this->GPvars['tx_enetcacheanalytics_foldState'] == '1' ? 1 : 0;
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
	 * Set chosen number of data points and scale factor to testsuite object
	 *
	 * @return void
	 */
	protected function intitializeTestSuiteOptions() {
		if (isset($this->GPvars['tx_enetcacheanalytics_testSuiteOptionsSelectionDone'])) {
			$this->userData['performance_numberOfDataPoints'] = (int)$this->GPvars['tx_enetcacheanalytics_testSuiteOptionsSelection_numberOfDataPoints'];
			$this->userData['performance_scaleFactor'] = (int)$this->GPvars['tx_enetcacheanalytics_testSuiteOptionsSelection_scaleFactor'];
		}
		if ((int)$this->userData['performance_numberOfDataPoints'] < 3) {
			$this->userData['performance_numberOfDataPoints'] = 3;
		}
		if ((int)$this->userData['performance_scaleFactor'] <= 100) {
			$this->userData['performance_scaleFactor'] = 200;
		}

		$this->testSuite->setNumberOfDataPoints($this->userData['performance_numberOfDataPoints']);
		$this->testSuite->setScaleFactor($this->userData['performance_scaleFactor']);
	}

	/**
	 * Chose number of data points and scale factor
	 *
	 * @return void
	 */
	protected function renderTestSuiteOptionsSelectionSection() {
		$content = array();

		$content[] = tx_enetcacheanalytics_utility_formhelper::makeTextInput('testSuiteOptionsSelection_numberOfDataPoints', 5, $this->userData['performance_numberOfDataPoints']);
		$content[] = 'Number of data points to calculate for a test<br />';

		$content[] = tx_enetcacheanalytics_utility_formhelper::makeTextInput('testSuiteOptionsSelection_scaleFactor', 5, $this->userData['performance_scaleFactor']);
		$content[] = 'Scale factor: Increment every test point by this value (in percent, 100 = no scale up, 200 = double value)<br />';

		$content[] = tx_enetcacheanalytics_utility_formhelper::makeHiddenField('testSuiteOptionsSelectionDone', 1);
		return $this->finalizeCollapsableSection($content, 'Test suite options', 'TestSuiteOptions');
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
		return $this->finalizeCollapsableSection($content, 'Select testcases to run', 'Testcases');
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
		return $this->finalizeCollapsableSection($content, 'Select backends to run tests on', 'Backends');
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
		return $this->finalizeCollapsableSection($content, 'Select different message types to show', 'Messages');
	}

	/**
	 * Show checkboxes to select graph and/or table output
	 *
	 * @returnt string HTML of selection
	 */
	protected function renderStatisticsRendererSelectionSection() {
		$content = array();
		$possibleRenderer = array('Graph', 'Table');
		foreach ($possibleRenderer as $renderer) {
			$selected = TRUE;
			$userUc = $this->userData['performance_selectedRenderer'];
			if (isset($this->GPvars['tx_enetcacheanalytics_rendererSelectionDone'])) {
				$selected = $this->GPvars['tx_enetcacheanalytics_rendererSelection'][$renderer] ? TRUE : FALSE;
				$userUc[$renderer] = $selected;
			} elseif (isset($userUc[$renderer])) {
				$selected = $userUc[$renderer];
			}
			$this->userData['performance_selectedRenderer'] = $userUc;
			$content[] = tx_enetcacheanalytics_utility_formhelper::makeCheckbox('rendererSelection', 1, $selected, $renderer);
			$content[] = $renderer . '<br />';
		}
		$content[] = tx_enetcacheanalytics_utility_formhelper::makeHiddenField('rendererSelectionDone', 1);
		return $this->finalizeCollapsableSection($content, 'Show gathered data as', 'Renderer');
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
	 * Render statistics output with selected renderers
	 *
	 * @return string HTML
	 */
	protected function renderStatistics() {
		$userUc = $this->userData['performance_selectedRenderer'];
		$content = array();
		if ($userUc['Graph']) {
			$content[] = $this->renderStatisticsGraph();
		}
		if ($userUc['Table']) {
			$content[] = $this->renderStatisticsTable();
		}
		return implode(chr(10), $content);
	}

	/**
	 * @return string HTML of statistic table
	 */
	protected function renderStatisticsTable() {
		$tableRenderer = t3lib_div::makeInstance('tx_enetcacheanalytics_bemodule_performance_view_ResultTable', $this);
		$content = $tableRenderer->render();
		$testsuiteRuntime = $this->testSuite->getRuntime();
		return $this->finalizeSection(array($content), 'Testsuite runtime: ' . $this->formatTimeMessage($testsuiteRuntime['value']));
	}

	/**
	 * @return string HTML of statistic graphs
	 */
	protected function renderStatisticsGraph() {
		$graphRenderer = t3lib_div::makeInstance('tx_enetcacheanalytics_bemodule_performance_view_ResultGraph', $this);
		$content = $graphRenderer->render();
		$testsuiteRuntime = $this->testSuite->getRuntime();
		return $this->finalizeSection(array($content), 'Testsuite runtime: ' . $this->formatTimeMessage($testsuiteRuntime['value']));
	}

	/**
	 * Format time statistics
	 *
	 * @return string Formatted time
	 */
	public static function formatTimeMessage($value) {
		return sprintf("%.3f", $value);
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

	/**
	 * Wrap content in collapsable section
	 */
	protected function finalizeCollapsableSection($contentArray, $sectionName, $sectionIdentifier) {
		$collapsed = $this->userData['performance_collapsed' . $sectionIdentifier] == 1 ? TRUE : FALSE;
		$sectionName = '<img title="Expand" alt="Expand" onclick="setAction(\'fold\'); setFieldValue(\'foldType\', \'' . $sectionIdentifier . '\'); setFieldValue(\'foldState\', \'' . ($collapsed ? 0 : 1) . '\'); document.enetcacheanalytics.submit();" src="../../../../typo3/gfx/select' . ($collapsed ? 'all' : 'none') . '.gif" style="cursor: pointer;" />&nbsp;' . $sectionName;
		if ($collapsed) {
			array_unshift($contentArray, '<div style="visibility:hidden; height:0px;">');
			$contentArray[] = '</div>';
			$content = $this->pObj->getSection($sectionName, implode(chr(10), $contentArray), 0, 1);
		} else {
			$content = $this->pObj->getSection($sectionName, implode(chr(10), $contentArray), 0, 1);
		}
		return $content;
	}
} // end of class
?>
