<?php

class tx_enetcacheanalytics_ExtDirectAnalyze {

	public function getLogEntries($parameters) {
		$where = '';
		if (strlen($parameters->unique_id) > 0) {
			$where = 'unique_id=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($parameters->unique_id, 'tx_enetcache_log');
		}
		$entries = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tx_enetcache_log',
			$where
		);

		return array(
			'length' => count($entries),
			'data' => $entries,
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
}
?>
