<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2010 Christian Kuhn <lolli@schwarzbu.ch>
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
 * Module 'Enetcache Analytics' for the 'enetcacheanalytics' extension.
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Michael Knabe <mk@e-netconsulting.de>
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_module1 extends t3lib_SCbase {
	/**
	 * @const Extension Key
	 */
	const extKey = 'enetcacheanalytics';

	/**
	 * @var string Absolute path of extension directory
	 */
	protected $extPath = '';

	/**
	 * @var array Global get/post vars of this module
	 */
	protected $GPvars = array();

	/**
	 * @var string HTML of main content marker
	 */
	protected $contentMarker = '';

	/**
	 * Default constructor sets several class constants
	 *
	 * @return void
	 */
	public function __construct() {
			// Sets absolute extPath
		$this->extPath = t3lib_extMgm::extPath(self::extKey);

			// Initialize GPvars array
		$this->GPvars = t3lib_div::_GP('DATA');
		if (!is_array($this->GPvars)) {
			$this->GPvars = array();
		}

		parent::init();
	}

	/**
	 * Render and echo out module content
	 *
	 * @return	void
	 */
	public function render() {
			// Initialize doc
		$this->setDocDefaults();

			// Call submodule
		$this->renderMainModule();

			// Set markers for template file
		$markers = array(
			'CONTENT' => $this->contentMarker,
		);

			// Render full page content
		$content = $this->doc->startPage('enet content cache analytics tool');
		$content .= $this->doc->moduleBody(array(), array(), $markers);
		$content .= $this->doc->endPage();

		echo $content;
	}


	/**
	 * Dispatch and render different submodules
	 *
	 * @return string HTML of submodule
	 */
	protected function renderMainModule() {
		$pageRenderer = $this->doc->getPageRenderer();
		$pageRenderer->loadExtJS();
		$pageRenderer->addExtDirectCode();

		$this->doc->setExtDirectStateProvider();
		$pageRenderer->addJsFile('ajax.php?ajaxID=ExtDirect::getAPI&namespace=' . 'TYPO3.EnetcacheAnalytics', NULL, FALSE);

		$pageRenderer->addJsFile('../t3lib/js/extjs/ux/Ext.ux.FitToParent.js');

		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('enetcacheanalytics') . 'res/js/ux/RowPanelExpander.js');

		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('enetcacheanalytics') . 'res/js/enetcacheAnalytics-Components.js');
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('enetcacheanalytics') . 'res/js/enetcacheAnalytics-Layouts.js');
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('enetcacheanalytics') . 'res/js/enetcacheAnalytics-Analyze.js');
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('enetcacheanalytics') . 'res/js/enetcacheAnalytics-Performance.js');
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('enetcacheanalytics') . 'res/js/enetcacheAnalytics-App.js');

		$settings = $GLOBALS['BE_USER']->uc['moduleData']['enetcacheanalytics'];
		if (!is_array($settings)) {
			$settings = array();
		}
		if (!is_array($settings['State'])) {
			$settings['State'] = array();
		}
		if (!is_array($settings['performance'])) {
			$settings['performance'] = array();
		}
		if (!is_array($settings['performance']['settings'])) {
			$settings['performance']['settings'] = array();
		}
		if (!isset($settings['performance']['settings']['dataPoints'])) {
			$settings['performance']['settings']['dataPoints'] = 3;
		}
		if (!isset($settings['performance']['settings']['scaleFactor'])) {
			$settings['performance']['settings']['scaleFactor'] = 200;
		}
		if (!is_array($settings['performance']['enabledBackends'])) {
			$settings['performance']['enabledBackends'] = array();
		}
		$pageRenderer->addInlineSettingArray('enetcacheAnalytics', $settings);

		$this->contentMarker = '<div id="tx-enetcacheanalytics-mod-grid"></div>';
	}

	/**
	 * Set document defaults
	 * Instantiates BE template class and sets defaults of object
	 *
	 * @return void
	 */
	protected function setDocDefaults() {
			// Create an instance of template
		$this->doc = t3lib_div::makeInstance('template');

			// Set back path of this module
		$this->doc->backPath = $GLOBALS['BACK_PATH'];

			// Main template
		$this->doc->setModuleTemplate('EXT:' . self::extKey . '/res/templates/mod1.html');

			// Additional styles
		$this->doc->addStyleSheet(self::extKey . '_css', t3lib_extMgm::extRelPath(self::extKey) . 'res/css/analyzer.css');

			// Default docType
		$this->doc->docType='xhtml_trans';

			// Default form tag
		$this->doc->form = '';
	}
}

	// Standard XCLASS definition
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/enetcacheanalytics/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/enetcacheanalytics/mod1/index.php']);
}

	// This checks permissions and exits if the users has no permission for entry
$BE_USER->modAccess($MCONF, 1);

	// Instantiate and run module
$SOBE = t3lib_div::makeInstance('tx_enetcacheanalytics_module1');
$SOBE->render();
?>