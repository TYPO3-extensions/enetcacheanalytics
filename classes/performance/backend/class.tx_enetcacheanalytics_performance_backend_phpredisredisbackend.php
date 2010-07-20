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
 * Class test implementation for redis backend with phpredis
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_performance_backend_PhpredisRedisBackend extends tx_enetcacheanalytics_performance_backend_AbstractBackend {
	/**
	 * Redis Daemon server
	 */
	const redisHost = '127.0.0.1';
	const redisPort = '6379';

	/**
	 * Redis operation counter when test start
	 */
	protected $setCountStart;
	protected $getCountStart;

	/**
	 * Set up this backend
	 */
	public function setUp() {
		$this->testRedisAvailable();

		$this->backend = t3lib_div::makeInstance(
			'tx_rediscache_cache_backend_RedisBackend',
			array(
				'identifierPrefix' => 'typo3.local-enecacheanalytics%',
				'hostname' => 'self::redisHost',
				'port' => 'self::redisPort',
			)
		);

		$this->backend->setCache($this->getMockFrontend());
	}

	protected function testRedisAvailable() {
			// Phpredis redis backend is only available since 4.5
			// Check for file existance to see if we can run this backend
		if (!is_file(PATH_t3lib . 'cache/backend/class.t3lib_cache_backend_phpredisredisbackend.php')) {
			throw t3lib_div::makeInstance('tx_enetcacheanalytics_exception_UnavailableBackend',
				'Phpredis backend not available with this TYPO3 version',
				1279584649
			);
		}
		if (!extension_loaded('redis')) {
			throw t3lib_div::makeInstance('tx_enetcacheanalytics_exception_UnavailableBackend',
				'redis php extension was not available',
				1279584646
			);
		}
		try {
			if (!@fsockopen(self::redisHost, self::redisPort)) {
				throw t3lib_div::makeInstance('tx_enetcacheanalytics_exception_UnavailableBackend',
					'redis sever not available',
					1279584647
				);
			}
		} catch (Exception $e) {
			throw t3lib_div::makeInstance('tx_enetcacheanalytics_exception_UnavailableBackend',
				'redis sever not available',
				1279584648
			);
		}
	}

	public function tearDown() {
		$this->backend->flush();
	}
}
?>
