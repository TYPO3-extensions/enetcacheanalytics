<?php

class tx_enetcacheanalytics_ExtDirectAnalyze {

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

	protected static function unserializeCallerField($callerField) {
		$callerField = unserialize($callerField);

		$result = array();
		foreach ($callerField as $k => $v) {
			$result[] = $k . ': ' . $v . '<br />';
		}

		return implode(chr(10), $result);
	}
}
?>
