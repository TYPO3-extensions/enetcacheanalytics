<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Christian Kuhn <lolli@schwarzbu.ch>
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
 * Class ExtDirectServer of backend module to fetch data.
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_ExtDirectServer {

	/**
	 * Method concerning cache log analyzer tab to get log entries
	 *
	 * @param  $parameters array
	 * @return array
	 */
	public function getLogEntries($parameters) {
		$where = '';
		if (strlen($parameters->unique_id) > 0) {
			$where = 'unique_id=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($parameters->unique_id, 'tx_enetcache_log');
		}
		$logRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tx_enetcache_log',
			$where
		);

		$data = array();
		$time = 0;
		foreach ($logRows as $row) {
			if ($time === 0) {
				$time = $row['microtime'];
			}
			$elapsedTime = $row['microtime'] - $time;
			$row['time'] = $elapsedTime;

			$row['caller'] = self::unserializeCallerField($row['caller']);

			if ($row['fe_user'] > 0) {
				$userName = 'FE: ' . $row['fe_user'];
			} elseif ($row['be_user'] > 0) {
				$userName = 'BE: ' . $row['be_user'];
			} else {
				$userName = '';
			}
			$row['user'] = $userName;

			$tags = @unserialize($row['tags']);
			if ($tags) {
				$row['tags'] = implode('<br />', $tags);
			} else {
				$row['tags'] = '';
			}

			$identifierSource = @unserialize($row['identifier_source']);
			if ($identifierSource) {
				$row['identifier_source'] = t3lib_utility_Debug::viewArray($identifierSource);
			} else {
				$row['identifier_source'] = '';
			}

			$htmlData = @unserialize($row['data']);
			if (is_array($htmlData)) {
				$row['data'] = t3lib_utility_Debug::viewArray($htmlData);
			} elseif (strlen($htmlData) > 0) {
				$row['data'] = htmlspecialchars($htmlData);
			} else {
				$row['data'] = '';
			}

			$data[] = $row;
		}

		return array(
			'length' => count($data),
			'data' => $data,
		);
	}

	/**
	 * Method concerning cache log analyzer tab to get log group for group drop down
	 *
	 * @return array
	 */
	public function getLogGroups() {
		$groupRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT unique_id, tstamp, page_uid',
			'tx_enetcache_log',
			'',
			'',
			'tstamp DESC'
		);

		$data = array();
		foreach ($groupRows as $row) {
			$formatDate = date('d/m/Y H:i:s', $row['tstamp']);
			$formatPageID = ($row['page_uid'] > 0) ? ' PID:' . $row['page_uid'] : '';
			$title = $formatDate . $formatPageID;

			$data[] = array(
				'unique_id' => $row['unique_id'],
				'title' => $title,
			);
		}

		return array(
			'length' => count($data),
			'data' => $data,
		);
	}

	/**
	 * Method concerning cache log analyzer tab to get stats of a specific log group
	 *
	 * @param  $parameters array
	 * @return array
	 */
	public function getLogStats($parameters) {
		$where = '';
		if (strlen($parameters->unique_id) > 0) {
			$where = 'unique_id=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($parameters->unique_id, 'tx_enetcache_log');
		}
		$logRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tx_enetcache_log',
			$where
		);

		$numberOfPlugins = 0;
		$numberOfEnetcachePlugins = 0;
		$numberOfSuccessfulGets = 0;

		foreach ($logRows as $row) {
			switch ($row['request_type']) {
				case 'COBJ START':
					$numberOfPlugins ++;
					break;
				case 'GET':
					if (strlen($row['data']) > 0) {
						$numberOfSuccessfulGets ++;
					}
					$numberOfEnetcachePlugins ++;
					break;
			}
		}

		$data = array(
			'unique_id' => $parameters->unique_id,
			'numberOfPlugins' => $numberOfPlugins,
			'numberOfEnetcachePlugins' => $numberOfEnetcachePlugins,
			'numberOfSuccessfulGets' => $numberOfSuccessfulGets,
		);

		return array(
			'length' => count($data),
			'data' => $data
		);
	}

	/**
	 * Helper method of cache log tab to unserialize a log entry backtrace
	 *
	 * @static
	 * @param  $callerField String serialized backtrace
	 * @return string
	 */
	protected static function unserializeCallerField($callerField) {
		$callerField = unserialize($callerField);

		$result = array();
		foreach ($callerField as $k => $v) {
			$result[] = $k . ': ' . $v . '<br />';
		}

		return implode(chr(10), $result);
	}

	/**
	 * Method concerning performance tab to get all available test entries
	 *
	 * @return array
	 */
	public function getTestEntries() {
		$data = array();
		$data[] = array('uid' => 0, 'name' => 'SetMultipleTimes');
		$data[] = array('uid' => 1, 'name' => 'GetMultipleTimes');
		$data[] = array('uid' => 2, 'name' => 'SetSingleTag');
		$data[] = array('uid' => 3, 'name' => 'GetByIdentifier');
		$data[] = array('uid' => 4, 'name' => 'DropBySingleTag');
		$data[] = array('uid' => 5, 'name' => 'SetKiloBytesOfData');
		$data[] = array('uid' => 6, 'name' => 'GetKiloBytesOfData');
		$data[] = array('uid' => 7, 'name' => 'SetMultipleTags');
		$data[] = array('uid' => 8, 'name' => 'DropMultipleTags');
		$data[] = array('uid' => 9, 'name' => 'FlushSingleTag');
		$data[] = array('uid' => 10, 'name' => 'FlushMultipleTags');

		return array(
			'length' => count($data),
			'data' => $data,
		);
	}

	/**
	 * Method concerning performance tab to get all available backends
	 *
	 * @return array
	 */
	public function getBackends() {
		$data = array();
		$data[] = array('uid' => 0, 'name' => 'dbBackend');
		$data[] = array('uid' => 1, 'name' => 'redisBackend');

		return array(
			'length' => count($data),
			'data' => $data,
		);
	}

	/**
	 * Get a setting from user UC
	 *
	 * @param  $name Setting name
	 * @return mixed Value on success, else false
	 */
	public function loadSetting($name) {
		$value = FALSE;
		$settings = $GLOBALS['BE_USER']->getModuleData('tools_enetcacheanalytics');
		if (isset($settings[$name])) {
			$value = $settings[$name];
		}
		
		return $value;
	}

	/**
	 * Save setting in user UC
	 *
	 * @param string $name Setting index
	 * @param mixed $value The value
	 * @return boolean True on success
	 */
	public function saveSetting($name, $value) {
		$settings = $GLOBALS['BE_USER']->getModuleData('tools_enetcacheanalytics');
		$settings[$name] = $value;
		$GLOBALS['BE_USER']->pushModuleData('tools_enetcacheanalytics', $settings);
		return TRUE;
	}
}
?>