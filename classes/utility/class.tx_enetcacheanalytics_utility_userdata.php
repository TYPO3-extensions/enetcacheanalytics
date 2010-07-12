<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2010 Christian Kuhn <lolli@schwarzbu.ch>
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
 * Class 'UserData' for the 'enetcacheanalytics' extension.
 * Store and update selected be_user module form data in be_user uC
 *
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 */
class tx_enetcacheanalytics_utility_UserData extends tx_enetcacheanalytics_utility_Data implements t3lib_Singleton {
	/**
	 * @var array List of user data latest form selections
	 */
	protected $data = array(
		'function' => string, // Selected submodule
		'performance_selectedBackends' => array(), // Selected backend of performance module
		'performance_selectedTestcases' => array(), // Selected testcases in performance module
		'performance_selectedMessages' => array(), // Selected messages to view in performance module
		'performance_selectedRenderer' => array(), // Selected messages to view in performance module
	);

	/**
	 * Initialize user specific module data
	 *
	 * @return void
	 */
	public function __construct() {
		$moduleUc = $GLOBALS['BE_USER']->getModuleData('tools_txenetcacheanalyticsM1');

		if (is_string($moduleUc['function'])) {
			$this['function'] = $moduleUc['function'];
		}
		if (is_string($moduleUc['performance_selectedBackends'])) {
			$this['performance_selectedBackends'] = unserialize($moduleUc['performance_selectedBackends']);
		}
		if (is_string($moduleUc['performance_selectedTestcases'])) {
			$this['performance_selectedTestcases'] = unserialize($moduleUc['performance_selectedTestcases']);
		}
		if (is_string($moduleUc['performance_selectedMessages'])) {
			$this['performance_selectedMessages'] = unserialize($moduleUc['performance_selectedMessages']);
		}
		if (is_string($moduleUc['performance_selectedRenderer'])) {
			$this['performance_selectedRenderer'] = unserialize($moduleUc['performance_selectedRenderer']);
		}
	}

	/**
	 * Return array with performance_selectedMessages set to 1
	 *
	 * @return array Enabled messages
	 */
	public function getEnabledMessages() {
		$enabledMessages = array();
		$messages = $this['performance_selectedMessages'];
		foreach ($messages as $messageType => $enabled) {
			if ($enabled) {
				$enabledMessages[] = $messageType;
			}
		}
		return $enabledMessages;
	}

	/**
	 * Store new user module data in db
	 *
	 * @return void
	 */
	public function persist() {
		$moduleUc = array();
		$moduleUc['function'] = $this['function'];
		$moduleUc['performance_selectedBackends'] = serialize($this['performance_selectedBackends']);
		$moduleUc['performance_selectedTestcases'] = serialize($this['performance_selectedTestcases']);
		$moduleUc['performance_selectedMessages'] = serialize($this['performance_selectedMessages']);
		$moduleUc['performance_selectedRenderer'] = serialize($this['performance_selectedRenderer']);
		$GLOBALS['BE_USER']->pushModuleData('tools_txenetcacheanalyticsM1', $moduleUc);
	}
}
?>
