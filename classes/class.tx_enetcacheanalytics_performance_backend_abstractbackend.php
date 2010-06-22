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
 * Abstract test backend implementation class
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
abstract class tx_enetcacheanalytics_performance_backend_AbstractBackend implements tx_enetcacheanalytics_performance_backend_Backend {
	/**
	 * Message types
	 */
	const INFO = -1;
	const TIME = 0;

	/**
	 * @var string Backend name / identifier, set in __construct() of backends
	 */
	protected $name = '';

	/**
	 * @var t3lib_cache_backend_Backend Instance of the cache backend
	 */
	protected $backend;

	/**
	 * @var integer microtime when test starts
	 */
	protected $timeStart;

	/**
	 * Default constructor sets backend name
	 */
	public function __construct() {
		$this->name = str_replace('tx_enetcacheanalytics_performance_backend_', '', get_class($this));
	}

	/**
	 * Set number of cache entries to backend without tag
	 */
	public function set($numberOfEntries = 100) {
		$prefix = 'entry_' . $numberOfEntries . '_';

			// Each entry has ~10kB of data
		$data = str_repeat('0123456789', 1000);

		$this->timeTrackStart();
		for ($i = 0; $i < $numberOfEntries; $i ++) {
			$this->backend->set($prefix . $i, $data, array(), 10000);
		}
		$message = array();
		$message[] = $this->getTimeTakenMessage();
		return $message;
	}

	public function setSingleTag($numberOfEntries = 100) {
		$prefix = 'entry_' . $numberOfEntries . '_';

			// Each entry has ~10kB of data
		$data = str_repeat('0123456789', 1000);

		$this->timeTrackStart();
		for ($i = 0; $i < $numberOfEntries; $i ++) {
			$this->backend->set($prefix . $i, $data, array($prefix . $i), 10000);
		}
		$message = array();
		$message[] = $this->getTimeTakenMessage();
		return $message;
	}

	public function setKiloBytesOfData($dataSizeInKB = 100) {
		$numberOfEntries = 40;
		$prefix = 'entry_' . $numberOfEntries . '_';

			// Each entry has 10kB of data
		$data = str_repeat('1', $dataSizeInKB * 1024);

		$this->timeTrackStart();
			// Set data 40 times to get high enough runtime
		for ($i = 0; $i < $numberOfEntries; $i ++) {
			$this->backend->set($prefix . $i, $data, array(), 10000);
		}
		$message = array();
		$message[] = $this->getTimeTakenMessage();
		return $message;
	}

	/**
	 * Set 100 entries with different number of tags to backend
	 * The tags are shifted for each entry:
	 *  entry 0 gets tags 0 to $numberOfTags
	 *  entry 1 gets tags 1 to $numberOfTags + 1
	 *  entry 2 gets tags 2 to $numberOfTags + 2
	 *  ...
	 */
	public function setMultipleTags($numberOfTags = 100) {
		$numberOfEntries = 100;
		$prefix = 'entry_' . $numberOfTags . '_';

			// Use 1 kb of data
		$data = str_repeat('1', 1024);

		$this->timeTrackStart();
		for ($i = 0; $i < $numberOfEntries; $i ++) {
			$tags = array();
				// @TODO: Use array_shift & array_pop here to scale O(1) for this calculation
			for ($j = $i; $j < ($i + $numberOfTags); $j ++) {
				$tags[] = 'multiple' . $j;
			}
			$this->backend->set($prefix . $i, $data, $tags, 10000);
		}
		$message = array();
		$message[] = $this->getTimeTakenMessage();
		return $message;
	}

	/**
	 * Drop previously set cache entries with multiple tags
	 */
	public function dropMultipleTags($numberOfTags = 100) {
			// @TODO: Refactor as class constant and combine with setMultipleTags()
		$numberOfEntries = 100;

		$this->timeTrackStart();
		for ($i = 0; $i < ($numberOfEntries + $numberOfTags); $i ++) {
			$this->backend->flushByTag('multiple' . $i);
		}
		$message = array();
		$message[] = $this->getTimeTakenMessage();
		return $message;
	}

	public function get($numberOfEntries = 100) {
		$prefix = 'entry_' . $numberOfEntries . '_';

		$numberOfCacheMisses = 0;
		$this->timeTrackStart();
		for ($i = 0; $i < $numberOfEntries; $i ++) {
			$result = $this->backend->get($prefix . $i);
			if (!$result) {
				$numberOfCacheMisses ++;
			}
		}

		$message = array();
		$message[] = $this->getTimeTakenMessage();
		$message[] = array(
			'type' => self::INFO,
			'value' => $numberOfCacheMisses,
			'message' => 'Cache misses',
		);
		return $message;
	}

	public function dropBySingleTag($numberOfEntries = 100) {
		$prefix = 'entry_' . $numberOfEntries . '_';

		$this->timeTrackStart();
		for ($i = 0; $i < $numberOfEntries; $i ++) {
			$this->backend->flushByTag($prefix . $i);
		}

		$message = array();
		$message[] = $this->getTimeTakenMessage();
		return $message;
	}

	/**
	 * Get backend name
	 *
	 * @return string Backend name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Flush previously set data from backend
	 *
	 * @return void
	 */
	public function flush() {
		$this->timeTrackStart();
		$this->backend->flush();
		$message = array();
		$message[] = $this->getTimeTakenMessage();
		return $message;
	}

	/**
	 * Get an instance of a mockend cache frontend class that returns an identifier
	 *
	 * @return tx_enetcacheanalytics_performance_utility_MockFrontend
	 */
	protected function getMockFrontend() {
		return t3lib_div::makeInstance('tx_enetcacheanalytics_performance_utility_MockFrontend');
	}

	protected function getTimeTakenMessage() {
		$message = array(
			'type' => self::TIME,
			'value' => $this->getTimeTaken(),
			'message' => 'Seconds taken',
		);
		return $message;
	}
	protected function timeTrackStart() {
		$this->timeStart = microtime(1);
	}
	protected function getTimeTaken() {
		return (microtime(1) - $this->timeStart);
	}
} // end of class
?>
