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
		foreach ($logRows as $row) {
			$row['caller'] = self::unserializeCallerField($row['caller']);

			if ($row['fe_user'] > 0) {
				$userName = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
					'username',
					'fe_users',
					'uid=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($row['fe_user'], 'fe_users')
				);
				$userName = 'FE: ' . $userName['username'];
			} elseif ($row['be_user'] > 0) {
				$userName = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
					'username',
					'be_users',
					'uid=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($row['be_user'], 'be_users')
				);
				$userName = 'BE: ' . $userName['username'];
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
			if (strlen($htmlData) > 0) {
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
