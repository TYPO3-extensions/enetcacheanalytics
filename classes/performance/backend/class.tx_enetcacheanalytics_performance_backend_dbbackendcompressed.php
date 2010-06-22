<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010  Christian Kuhn <lolli@schwarzbu.ch>
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
 * Class test implementation for db backend
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_performance_backend_DbBackendCompressed extends tx_enetcacheanalytics_performance_backend_DbBackend {
	/**
	 * Constructor sets db tables of parent class
	 */
	public function __construct() {
		parent::__construct();
		self::$cacheTable = 'tx_enetcacheanalytics_performance_compressed';
		self::$tagsTable = 'tx_enetcacheanalytics_performance_compressed_tags';
	}

	/**
	 * Set up this backend
	 */
	public function setUp() {
			// isLoaded in 4.4 can throw exception if extension is not loaded,
			// but in 4.3 it die()'s
		if (!t3lib_extMgm::isLoaded('enetcache')) {
			throw t3lib_div::makeInstance('tx_enetcacheanalytics_exception_UnavailableBackend',
				'Extension enetcache not loaded',
				1277127766
			);
		}

		$this->createTables();

		$this->backend = t3lib_div::makeInstance(
			'tx_enetcache_cache_backend_CompressedDbBackend',
			array(
				'cacheTable' => self::$cacheTable,
				'tagsTable' => self::$tagsTable,
			)
		);

		$this->backend->setCache($this->getMockFrontend());
	}

	/**
	 * Content field in compressed db backend must be a blob
	 */
	protected function createTables() {
		parent::createTables();

		$GLOBALS['TYPO3_DB']->sql_query(
			'ALTER TABLE ' . self::$cacheTable . ' CHANGE content content mediumblob;'
		);
	}
}
?>
