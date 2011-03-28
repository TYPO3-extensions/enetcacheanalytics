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
 * tx_enetcacheanalytics_log
 *
 * Log data from enetcache to database table
 *
 * @author  Michael Knabe <mk@e-netconsulting.de>
 * @author  Christian Kuhn <lolli@schwarzbu.ch>
 * @package TYPO3
 * @subpackage enetcacheanalytics
 */
class tx_enetcacheanalytics_log extends tx_enetcache_hook implements t3lib_Singleton {
	/**
	 * @var string Unique ID of one request
	 */
	private $logId = '0';

	/**
	 * @var int Counter for get requests
	 */
	private $getCount = 0;

	/**
	 * @var int Counter for successful get requests
	 */
	private $getCountSuccessful = 0;


	public function __construct() {
		$this->logId = uniqid();
	}


	public function get($params) {
		if(is_array($params['cacheData'])) {
			$cachedContent = $params['cacheData']['data'];
		} else {
			$cachedContent = FALSE;
		}

			// Increment get counter and successful get counter
		$this->getCount ++;
		if ($cachedContent !== FALSE) {
			$this->getCountSuccessful ++;
		}

		$insertDataArray = $this->buildInsertDataArray('GET');
		$insertDataArray['identifier_source'] = serialize($params['identifiers']);
		$insertDataArray['identifier'] = md5($insertDataArray['identifier_source']);
		$insertDataArray['data'] = ($cachedContent === FALSE) ? 'FALSE' : serialize($cachedContent);
		$insertDataArray['tags'] = serialize($params['cacheData']);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery(
			'tx_enetcache_log',
			$insertDataArray
		);
	}

	public function set(&$params) {
			// Add identifier to tag array to enable direct drop of single entries
		$params['tags'][] = $params['hash'];

		$insertDataArray = $this->buildInsertDataArray('SET');
		$insertDataArray['identifier_source'] = serialize($params['identifiers']);
		$insertDataArray['identifier'] = md5($insertDataArray['identifier_source']);
		$insertDataArray['data'] = serialize($params['data']);
		$insertDataArray['tags'] = serialize($params['tags']);
		$insertDataArray['lifetime'] = $params['lifetime'];
		$GLOBALS['TYPO3_DB']->exec_INSERTquery(
			'tx_enetcache_log',
			$insertDataArray
		);
	}

	public function flush() {
		$insertDataArray = $this->buildInsertDataArray('FLUSH');
		$insertDataArray['identifier_source'] = '';
		$insertDataArray['identifier'] = '';
		$insertDataArray['data'] = '';
		$insertDataArray['tags'] = '';
		$insertDataArray['lifetime'] = '';
		$GLOBALS['TYPO3_DB']->exec_INSERTquery(
			'tx_enetcache_log',
			$insertDataArray
		);
	}

	public function drop(&$params) {
		$callerClass = $this->getCallerClassAndMethod();

		if ($callerClass['class'] != 'tx_enetcacheanalytics_cacheanalyzer') {
			$insertDataArray = $this->buildInsertDataArray('DROP');
			$insertDataArray['identifier_source'] = '';
			$insertDataArray['identifier'] = '';
			$insertDataArray['data'] = '';
			$insertDataArray['tags'] = serialize($params['tags']);
			$insertDataArray['lifetime'] = '';
			$GLOBALS['TYPO3_DB']->exec_INSERTquery(
				'tx_enetcache_log',
				$insertDataArray
			);
		}
	}

	public function getLogId() {
		return $this->logId;
	}

	public function getGetCount() {
		return $this->getCount;
	}
	public function getGetCountSuccessful() {
		return $this->getCountSuccessful;
	}

	/**
	 * Returns an array with the data that has to be inserted in every log entry.
	 *
	 * @param string Type of request. Usually one of get, set, drop or flush. Fallback to unknown if not set
	 * @return array The general log data
	 */
	protected function buildInsertDataArray($requestType = 'UNKNOWN') {
		$fe_user = 0;
		$page_uid = 0;

		if ($GLOBALS['TSFE']) {
			$fe_user = $GLOBALS['TSFE']->fe_user->user['uid'];
			$page_uid = $GLOBALS['TSFE']->id;
		}

		$be_user = 0;
		if ($GLOBALS['BE_USER']) {
			$be_user =  $GLOBALS['BE_USER']->user['uid'];
		}
		$result = array(
			'be_user' => $be_user,
			'caller' => serialize($this->getCallerClassAndMethod()),
			'content_uid' => $this->getCurrentRecordId(),
			'fe_user' => $fe_user,
			'microtime' => round(microtime(true) * 1000),
			'page_uid' => $page_uid,
			'request_type' => $requestType,
			'tstamp' => $GLOBALS['EXEC_TIME'],
			'unique_id' => $this->logId,
		);
		return $result;
	}

	/**
	 * Get caller information with some weird backtrace snafu
	 *
	 * @return array More or less useful caller class and method
	 */
	protected function getCallerClassAndMethod() {
		$caller = debug_backtrace(FALSE);

		foreach ($caller as $call) {
			if ($call['class'] == 'tx_enetcache') {
				$wantedCall = current($caller);
				break;
			}
		}

		$result = array_intersect_key(
			$wantedCall,
			array_flip(array('file', 'line', 'class', 'function'))
		);

		return $result;
	}

	/**
	 * Gets the id of the current calculated record
	 *
	 * @return int Record id or 0 if id couldn't be determined for same reason
	 */
	protected function getCurrentRecordId() {
		if ($GLOBALS['TSFE']->currentRecord) {
			$currentRecord = explode(':', $GLOBALS['TSFE']->currentRecord);
			if (isset($currentRecord[1])) {
				return ((int)$currentRecord[1]);
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}
}

?>
