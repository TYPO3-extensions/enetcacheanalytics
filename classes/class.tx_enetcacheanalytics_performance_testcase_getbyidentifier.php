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
 * Measures how a backend scales with a rising number of get's
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_performance_testcase_GetByIdentifier extends tx_enetcacheanalytics_performance_testcase_AbstractTestcase {
	/**
	 * Initialize by setting a number of entries, then measure time to get them
	 */
	public function run() {
		$stats = array();
		$numberOfEntries = array(100, 400, 1600);
		foreach ($numberOfEntries as $number) {
			$this->backend->set($number);
			$stats[$number] = $this->backend->get($number);
			$this->backend->flush();
		}
		return $stats;
	}
} // end of class
?>
