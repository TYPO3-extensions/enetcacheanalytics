<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Christian Kuhn <lolli@schwarzbu.ch>
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
 * Interface for performance test backends
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
interface tx_enetcacheanalytics_performance_backend_Backend {
	/**
	 * Instantiate and set up backend
	 */
	public function setUp();

	/**
	 * Cleanup backend
	 */
	public function tearDown();

	/**
	 * Get backend name
	 */
	public function getName();

	/**
	 * Flush previously set data from backend
	 */
	public function flush();

	/**
	 * Set a series of cache entries to backend without any tags
	 */
	public function set($numberOfEntries = 100);

	/**
	 * Set number of cache entries
	 */
	public function setSingleTag($numberOfEntries = 100);

	/**
	 * Set cache entries with variable data size
	 */
	public function setKiloBytesOfData($dataSizeInKB = 100);

	/**
	 * Set cache entries with variable number of tags
	 */
	public function setMultipleTags($numberOfTags = 100);

	/**
	 * Get number of cache entries by identifier
	 * Calculates same identifier as set() if feeded with same parameter
	 */
	public function get($numberOfEntries = 100);

	/**
	 * Drop cache entries by single tag
	 */
	public function dropBySingleTag($numberOfEntries = 100);

	/**
	 * Drop cache entries with multiple tags by single drop tag action
	 * Should be combined with setMultipleTags to set up required data
	 */
	public function dropMultipleTags($numberOfTags = 100);
}
?>
