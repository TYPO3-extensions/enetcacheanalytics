<?php

class tx_enetcacheanalytics_ExtDirectAnalyze {
	public function getEntries() {

		$entries = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'uid, title, description',
			'tx_enetextjsbase_domain_model_entry',
			''
		);

		return $entries;
	}
}
?>
