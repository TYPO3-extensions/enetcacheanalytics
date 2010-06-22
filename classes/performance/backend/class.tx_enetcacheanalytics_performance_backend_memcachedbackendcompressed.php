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
 * Class test implementation for compressed memcache backend
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_performance_backend_MemcachedBackendCompressed extends tx_enetcacheanalytics_performance_backend_MemcachedBackend {
	/**
	 * Set up this backend
	 */
	public function setUp() {
		$this->testMemcacheAvailable();

		$this->backend = t3lib_div::makeInstance(
			't3lib_cache_backend_MemcachedBackend',
			array(
				'servers' => array(self::memcachedHost . ':' . self::memcachedPort),
				'compression' => TRUE,
			)
		);

		$this->backend->setCache($this->getMockFrontend());
	}
}
?>
