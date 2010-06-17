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
	 * @var array Gathered output of performance tests
	 */
	protected $testStatistics = array();

	/**
	 * Default init method, required by interface
	 *
	 * @return void
	 */
	public function init(tx_enetcacheanalytics_module1 &$pObj) {
		$this->pObj = $pObj;
		$this->GPvars = $pObj->getGPvars();
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
				$testRunner = t3lib_div::makeInstance('tx_enetcacheanalytics_performance_TestRunner');
				$testRunner->run();
				$this->testStatistics = $testRunner->getTestResults();
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
		return (implode(chr(10), $content));
	}

	/**
	 * @return string
	 */
	protected function renderMainModuleContent() {
		if (count($this->testStatistics)) {
			debug($this->testStatistics);
		}
		$content = array();
		$content[] = '<input type="submit" onClick="setAction(\'performTests\');" value="Run performance tests" />';
		return(implode(chr(10), $content));
	}
} // end of class
?>
