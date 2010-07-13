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
 * Measures how a backend scales with dropping cache entries with multiple tags
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_performance_testcase_DropMultipleTags extends tx_enetcacheanalytics_performance_testcase_AbstractTestcase {
	protected $startValue = 20;

	/**
	 * Drop entries with multiple tags by single tag from backend
	 */
	public function run() {
		$stats = array();
		for ($i = 1; $i <= $this->numberOfDataPoints; $i ++) {
			$currentValue = $currentValue ? $this->getNextDataValue($currentValue) : $this->startValue;
			$this->backend->setMultipleTags($currentValue);
			$stats[$currentValue] = $this->backend->dropMultipleTags($currentValue);
			$this->backend->flush();
		}
		return $stats;
	}
} // end of class
?>
