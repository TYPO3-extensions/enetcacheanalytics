<?php
$TYPO3_CONF_VARS['FE']['eID_include']['tx_kresscache'] = 'EXT:enetcache/classes/class.tx_enetcache_log_analytics.php';


$TYPO3_CONF_VARS['EXTCONF']['enetcache']['hooks']['tx_enetcache'][] = 'tx_enetcacheanalytics_log';

	// Add new entry to cache handling (flash icon in toolbar menu) to delete log elements
$TYPO3_CONF_VARS['BE']['AJAX']['enetcacheanalytics::truncateLogTables'] = 'EXT:enetcacheanalytics/hooks/class.tx_enetcacheanalytics_backendContentCacheAction.php:tx_enetcacheanalytics_backendContentCacheAction->truncateLogTables';
$TYPO3_CONF_VARS['SC_OPTIONS']['additionalBackendItems']['cacheActions'][] = "EXT:enetcacheanalytics/hooks/class.tx_enetcacheanalytics_backendContentCacheAction.php:tx_enetcacheanalytics_backendContentCacheAction";

	// Add hook to tslib_content cObjGetSingle to log all USER object (plugin) calls
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = array(
	'USER',
	'EXT:enetcacheanalytics/hooks/class.tx_enetcacheanalytics_tslibContent_cObjTypeAndClass.php:&tx_enetcacheanalytics_tslibContent_cObjTypeAndClass',
);

	// Print statistics add end of page as html comment to page foot
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['hook_eofe']['enetcacheanalytics'] =
	'EXT:enetcacheanalytics/hooks/class.tx_enetcacheanalytics_eofe.php:&tx_enetcacheanalytics_eofe->printStatistics';
?>
