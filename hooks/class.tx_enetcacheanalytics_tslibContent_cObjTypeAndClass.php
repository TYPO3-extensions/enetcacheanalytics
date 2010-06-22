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
	public function cObjGetSingleExt($name, $conf, $TSkey, $pObj) {
		$time_before = round(microtime(true) * 1000);

		$logId = t3lib_div::makeInstance('tx_enetcacheanalytics_log')->getLogId();
		$funcRef = explode('->', $conf['userFunc']);

		$insertData = array(
			'fe_user' => $GLOBALS['TSFE']->fe_user->user['uid'],
			'be_user' => $GLOBALS['BE_USER']->user['uid'],
			'caller' => serialize(array('class' => $funcRef[0], 'function' => $funcRef[1])),
			'content_uid' =>  $pObj->data['uid'],
			'page_uid' =>  $pObj->data['pid'],
			'unique_id' => $logId,
			'tstamp' => $GLOBALS['EXEC_TIME'],
			'microtime' => $time_before,
			'request_type' => 'USER',
		);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery(
			'tx_enetcache_log',
			$insertData
		);
		$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();

		$content .= $pObj->USER($conf);

		$time_after = round(microtime(true) * 1000);
		$parseTime = $time_after - $time_before;

		$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'tx_enetcache_log',
			'uid=' . $uid,
			array('data' => $parseTime)
		);
		return $content;
	}
}

?>
