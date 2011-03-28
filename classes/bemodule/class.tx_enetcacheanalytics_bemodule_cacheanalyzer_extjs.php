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
 * Function 'Overview' for the 'enetcacheanalytics' BE module.
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Michael Knabe <mk@e-netconsulting.de>
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_bemodule_cacheanalyzer_extjs implements tx_enetcacheanalytics_bemodule {
	/**
	 * @var tx_enetcacheanalytics_module1 Default parent object
	 */
	protected $pObj = object;

	/**
	 * @var array Global get/post vars
	*/
	protected $GPvars = array();

	/**
	 * Default init method, required by interface
	 *
	 * @return void
	 */
	public function init(tx_enetcacheanalytics_module1 &$pObj) {
		$this->pObj = $pObj;
		$this->GPvars = $pObj->getGPvars();

			// @TODO: Write API for index.php
		$pageRenderer = $this->pObj->doc->getPageRenderer();
		$pageRenderer->loadExtJS();
		$pageRenderer->addExtDirectCode();
		$pageRenderer->addJsFile('ajax.php?ajaxID=ExtDirect::getAPI&namespace=' . 'TYPO3.EnetcacheAnalytics', NULL, FALSE);
		$pageRenderer->addJsFile(
			'../t3lib/js/extjs/ux/Ext.ux.FitToParent.js'
		);
		$pageRenderer->addJsFile(
			t3lib_extMgm::extRelPath('enetcacheanalytics') . 'res/js/enetcacheAnalyticsAnalyze.js'
		);
	}

	/**
	 * Default execute, required by interface
	 *
	 * @return void
	 */
	public function execute() {
		$this->pObj->setContentMarker('<div id="tx-enetcacheanalytics-mod-grid"></div>');
	}

} // end of class
?>
