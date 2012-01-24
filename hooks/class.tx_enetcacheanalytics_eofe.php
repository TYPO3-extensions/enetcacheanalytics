<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 e-netconsulting KG
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Hook for end-of-frontend processing.
 * Adds a HTML comment to end of page to show number of enetcache->get requests
 *
 * @access public
 * @package TYPO3
 * @subpackage enetcache
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_eofe {
	/**
	 * Print out element cache statistics
	 * Simple method to add a comment at end of page output
	 * Prints number of element cache handled elements and number of successfull cache requests
	 *
	 * @param	array			$params
	 * @param	tslib_fe		$pObj
	 * @return	void
	 */
	public function printStatistics($params, &$pObj) {
		$enetCacheAnalyticsObj = t3lib_div::makeInstance('tx_enetcacheanalytics_log');
		$getCount = $enetCacheAnalyticsObj->getGetCount();
		$getCountSuccessful = $enetCacheAnalyticsObj->getGetCountSuccessful();

		$GLOBALS['TSFE']->content .= '<!-- enetcache stats: Content element Engine was asked ' .
			$getCount .
			' times for elements, ' .
			$getCountSuccessful .
			' where answered with existing cache entries -->';
	}
}

?>
