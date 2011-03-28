<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2010 Michael Knabe <mk@e-netconsulting.de>
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
 * Hook class for tslibContent USER objects
 */
class tx_enetcacheanalytics_tslibContent_cObjTypeAndClass {

	/**
	 * Wrapper hook around cObj Type USER to log plugin runtime
	 *
	 * @param  $name
	 * @param  $conf
	 * @param  $TSkey
	 * @param  $pObj
	 * @return HTML content of called method
	 */
	public function cObjGetSingleExt($name, $conf, $TSkey, $pObj) {
		$funcRef = explode('->', $conf['userFunc']);

			// Insert log row before calling the object
		$insertData = array(
			'fe_user' => $GLOBALS['TSFE']->fe_user->user['uid'],
			'be_user' => $GLOBALS['BE_USER']->user['uid'],
			'caller' => serialize(array('class' => $funcRef[0], 'function' => $funcRef[1])),
			'content_uid' =>  $pObj->data['uid'],
			'page_uid' =>  $GLOBALS['TSFE']->id,
			'unique_id' => t3lib_div::makeInstance('tx_enetcacheanalytics_log')->getLogId(),
			'tstamp' => $GLOBALS['EXEC_TIME'],
			'microtime' => round(microtime(true) * 1000),
			'identifier' => '',
			'request_type' => 'COBJ START',
		);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery(
			'tx_enetcache_log',
			$insertData
		);

			// Call USER method
		$content = $pObj->USER($conf);

			// Insert a second log row after calling the object
		$insertData['microtime'] = round(microtime(true) * 1000);
		$insertData['request_type'] = 'COBJ END';
		$GLOBALS['TYPO3_DB']->exec_INSERTquery(
			'tx_enetcache_log',
			$insertData
		);

			// Return calculated content by user method
		return $content;
	}
}

?>