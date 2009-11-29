<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009  Christian Kuhn <lolli@schwarzbu.ch>
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
	 * @var string HTML of additional functions in docheader
	 */
	protected $additionalDocHeaderMarker = '';

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
		$this->extPath = t3lib_extMgm::extPath(tx_enetcacheanalytics_module1::extKey);

			// Initialize GPvars array
		$this->GPvars = t3lib_div::_GP('DATA');
		if (!is_array($this->GPvars)) {
			$this->GPvars = array();
		}

		parent::init();
	}

	/**
	 * Function drop down in docheader
	 *
	 * @return	void
	 */
	public function menuConfig() {
		$this->MOD_MENU = Array (
			'function' => Array (
				'cacheanalyzer' => 'Cache analyzer',
			)
		);
		parent::menuConfig();
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
		$this->renderSubModule();

			// Set markers for template file
		$markers = array(
			'CSH' => $docHeaderButtons['csh'],
			'FUNCTION_MENU' => t3lib_BEfunc::getFuncMenu(0, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function']),
			'ADDITIONAL_FUNCTIONS' => $this->additionalDocHeaderMarker,
			'CONTENT' => $this->contentMarker,
		);

			// Render full page content
		$content = $this->doc->startPage('enet content cache analytics tool');
		$content .= $this->doc->moduleBody(array(), array('csh' => ''), $markers);
		$content .= $this->doc->endPage();

		echo $content;
	}


	/**
	 * Dispatch and render different submodules
	 *
	 * @return string HTML of submodule
	 */
	protected function renderSubModule() {
		$module = '';
		$moduleGP = t3lib_div::_GP('SET');
		if ($moduleGP['function']) {
			$module = $moduleGP['function'];
		} else {
			$moduleUC = $GLOBALS['BE_USER']->getModuleData('tools_txenetcacheanalyticsM1');
			if (strlen($moduleUC['function']) > 0) {
				$module = $moduleUC['function'];
			}
		}
		switch ($module) {
			default:
				$moduleObject = t3lib_div::makeInstance('tx_enetcacheanalytics_cacheanalyzer');
		}
		if ($moduleObject instanceof tx_enetcacheanalytics_bemodule) {
			$moduleObject->init($this);
			$moduleObject->execute();
		}
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
		$this->doc->setModuleTemplate('EXT:' . tx_enetcacheanalytics_module1::extKey . '/res/mod1_template.html');

			// Additional styles
		$this->doc->addStyleSheet('enetcacheanalytics_css', t3lib_extMgm::extRelPath(tx_enetcacheanalytics_module1::extKey) . 'res/mod1.css');

			// Default dotType
		$this->doc->docType='xhtml_trans';

			// Default form tag
		$this->doc->form = '<form action="" method="post" name="' . tx_enetcacheanalytics_module1::extKey . '" enctype="multipart/form-data">';

			// JavaScript for main function seletor
		$this->doc->JScodeArray[] = '
			script_ended = 0;
			function jumpToUrl(URL)	{
				document.location = URL;
			}
		';

			// JavaScript to set post var data and handle data fields
		$this->doc->JScodeArray[] = '
			function setAction(action) {
				setFieldValue(\'action\', action);
			}
			function setFieldValue(name, value) {
					// Check for existing element, enable it and set value. else add new element as hidden input element
				if ( document.forms["enetcacheanalytics"].elements["DATA[tx_enetcacheanalytics_"+name+"]"] ) {
					document.forms["enetcacheanalytics"].elements["DATA[tx_enetcacheanalytics_"+name+"]"].disabled = false;
					document.forms["enetcacheanalytics"].elements["DATA[tx_enetcacheanalytics_"+name+"]"].value = value;
				} else {
					var newElement = document.createElement("input");
					newElement.setAttribute("name", "DATA[tx_enetcacheanalytics_"+name+"]");
					newElement.setAttribute("type", "hidden");
					newElement.setAttribute("value", value);
					document.forms["enetcacheanalytics"].appendChild(newElement);
				}
			}
		';
	}

	/**
	 * Get extension key
	 *
	 * @return string Extension key
	 */
	public function getExtKey() {
		return tx_enetcacheanalytics_module1::extKey;
	}

	/**
	 * Get absolute path to this extension
	 *
	 * @return string Path to this extension
	 */
	public function getExtPath() {
		return $this->extPath;
	}

	/**
	 * Get all Get / Post vars of the extension namespace
	 *
	 * @return array GPvars
	 */
	public function getGPvars() {
		return $this->GPvars;
	}

	/**
	 * Set additional JS to doc
	 *
	 * @param string Javascript to include
	 * @return void
	 */
	public function setAdditionalJavascript($javascript) {
		$this->doc->JScodeArray[] = $javascript;
	}

	/**
	 * Set additional function marker in doc header
	 *
	 * @param string HTML of marker
	 * @return void
	 */
	public function setAdditionalDocHeaderMarker($html) {
		$this->additionalDocHeaderMarker = $html;
	}

	/**
	 * Set main content marker
	 *
	 * @param string HTML of marker
	 * @return void
	 */
	public function setContentMarker($html) {
		$this->contentMarker = $html;
	}
} // End of class

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
