<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE == 'BE') {
	t3lib_extMgm::addModulePath('tools_txenetcacheanalyticsM1', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
	t3lib_extMgm::addModule('tools', 'txenetcacheanalyticsM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ExtDirect']['TYPO3.EnetcacheAnalytics.Analyze'] = t3lib_extMgm::extPath($_EXTKEY) . 'classes/extdirect/class.tx_enetcacheanalytics_extdirect_analyze.php:tx_enetcacheanalytics_ExtDirectAnalyze';

?>
