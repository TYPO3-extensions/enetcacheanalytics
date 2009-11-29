<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE == 'BE') {
	t3lib_extMgm::addModulePath('tools_txenetcacheanalyticsM1', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
	t3lib_extMgm::addModule('tools', 'txenetcacheanalyticsM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
}
?>
