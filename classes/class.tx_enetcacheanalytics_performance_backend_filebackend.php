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
 * Class test implementation for file backend
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_performance_backend_FileBackend extends tx_enetcacheanalytics_performance_backend_AbstractBackend {
	/**
	 * Directory for testing data, relative to PATH_site
	 */
	const cacheDirectory = 'typo3temp/enetcacheanalytics-performance/';

	/**
	 * Set up this backend
	 */
	public function setUp() {
		$this->backend = t3lib_div::makeInstance(
			't3lib_cache_backend_FileBackend',
			array(
				'cacheDirectory' => self::cacheDirectory,
			)
		);

		$this->backend->setCache($this->getMockFrontend());
	}

	public function tearDown() {
		$directory = $this->backend->getCacheDirectory();
		if (is_dir($directory)) {
			t3lib_div::rmdir($directory, TRUE);
		}
	}

	public function setCacheEntriesWithSingleTag($numberOfEntries = 100) {
		$this->queryCountStart();
		$message = parent::setCacheEntriesWithSingleTag($numberOfEntries);
		$message[] = $this->getQueryCountMessage();
		return $message;
	}

	public function getCacheEntriesWithSingleTagByIdentifier($numberOfEntries = 100) {
		$this->queryCountStart();
		$message = parent::getCacheEntriesWithSingleTagByIdentifier($numberOfEntries);
		$message[] = $this->getQueryCountMessage();
		return $message;
	}

	public function dropCacheEntriesBySingleTag($numberOfEntries = 100) {
		$this->queryCountStart();
		$message = parent::dropCacheEntriesBySingleTag($numberOfEntries);
		$message[] = $this->getQueryCountMessage();
		return $message;
	}

	protected function getQueryCountMessage() {
		$message = array(
			'type' => self::INFO,
			'value' => 'unknown',
			'message' => 'Queries performed',
		);
		return $message;
	}
	protected function queryCountStart() {
	}
}
?>
