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
class tx_enetcacheanalytics_performance_backend_PdoBackendSqlite extends tx_enetcacheanalytics_performance_backend_AbstractBackend {
	/**
	 * Path to sqlite db file
	 */
	protected static $dbFolder = '';
	protected static $dbName = '';

	/**
	 * Default constructor: Initialize path and filename
	 */
	public function __construct() {
		parent::__construct();
		self::$dbFolder = PATH_site . 'typo3temp/tx_enetcacheanalytics_pdobackendtest/';
		self::$dbName = 'sqlite.db';
	}

	/**
	 * Set up this backend
	 */
	public function setUp() {
			// pdo backend is only available since 4.4
			// Check for file existance to see if we can run this backend
		if (!is_file(PATH_t3lib . 'cache/backend/class.t3lib_cache_backend_pdobackend.php')) {
			throw new Exception ('Pdo backend not available', 1277127650);
		}

		if (!extension_loaded('pdo_sqlite')) {
			throw new Exception ('Pdo backend not available', 1277127970);
		}

			// Clean up from previous run if it still exists for some reason
		if (is_file(self::$dbFolder)) {
			t3lib_div::rmdir(self::$dbFolder, TRUE);
		}

		t3lib_div::mkdir(self::$dbFolder);

		$pdoHelper = t3lib_div::makeInstance('t3lib_PdoHelper', 'sqlite:' . self::$dbFolder . self::$dbName, '', '');
		$pdoHelper->importSql(PATH_t3lib . 'cache/backend/resources/ddl.sql');

		$this->backend = t3lib_div::makeInstance(
			't3lib_cache_backend_PdoBackend',
			array(
				'dataSourceName' => 'sqlite:' . self::$dbFolder . self::$dbName,
				'username' => '',
				'password' => '',
			)
		);

		$this->backend->setCache($this->getMockFrontend());
	}

	public function tearDown() {
		t3lib_div::rmdir(self::$dbFolder, TRUE);
	}
}
?>
