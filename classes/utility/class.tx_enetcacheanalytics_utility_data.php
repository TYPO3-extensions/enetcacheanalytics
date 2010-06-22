<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2010 Christian Kuhn <lolli@schwarzbu.ch>
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
 * Class 'data' for the 'enetcacheanalytics' extension.
 * Base class of all data objects
 *
 * Implements php SPL ArrayAccess to make an object data accessible like an array
 * Implements php SPL IteratorAggregate to iterate over object data with foreach
 *
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 */
abstract class tx_enetcacheanalytics_utility_Data implements ArrayAccess, IteratorAggregate {
	/**
	 * @var array Data array. Members must be declared explicitly in child classes
	 */
	protected $data = array();

	/**
	 * Defined by ArrayAccess interface
	 * Set a value given it's key
	 *
	 * @param mixed key (string or integer)
	 * @param mixed value
	 * @throws tx_enetcacheanalytics_exception_invaliddata if key is not declared
	 * @return void
	 */
	public function offsetSet($key, $value) {
		if (array_key_exists($key, $this->data)) {
			$this->data[$key] = $value;
		} else {
			throw t3lib_div::makeInstance('tx_enetcacheanalytics_exception_invaliddata',
				'Access to not defined array key. class / key: ' . $get_class($this) . ' ' . $key,
				1277236182
			);
		}
	}

	/**
	 * Defined by ArrayAccess interface
	 * Return a value given it's key
	 *
	 * @param mixed key (string or integer)
	 * @throws tx_enetcacheanalytics_exception_invaliddata if key is not declared
	 * @return mixed value
	 */
	public function offsetGet($key) {
		if (!array_key_exists($key, $this->data)) {
			throw t3lib_div::makeInstance('tx_enetcacheanalytics_exception_invaliddata',
				'Access to not defined array key. class / key: ' . $get_class($this) . ' ' . $key,
				1277236216
			);
		}
		return($this->data[$key]);
	}

	/**
	 * Defined by ArrayAccess interface
	 * Unset a value by it's key
	 *
	 * @param mixed key (string or integer)
	 * @throws tx_enetcacheanalytics_exception_invaliddata if key is not declared
	 * @return void
	 */
	public function offsetUnset($key) {
		if (array_key_exists($key, $this->data)) {
			unset($this->data[$key]);
		} else {
			throw t3lib_div::makeInstance('tx_enetcacheanalytics_exception_invaliddata',
				'Access to not defined array key. class / key: ' . $get_class($this) . ' ' . $key,
				1277236253
			);
		}
	}

	/**
	 * Defined by ArrayAccess interface
	 * Check if key exists
	 *
	 * @param mixed key (string or integer)
	 * @throws tx_enetcacheanalytics_exception_invaliddata if key is not declared
	 * @return boolean
	 */
	public function offsetExists($key) {
		if (array_key_exists($key, $this->data)) {
			return(TRUE);
		} else {
			throw t3lib_div::makeInstance('tx_enetcacheanalytics_exception_invaliddata',
				'Access to not defined array key. class / key: ' . $get_class($this) . ' ' . $key,
				1277236274
			);
		}
	}

	/**
	 * Defined by IteratorAggregate interface
	 * Returns an iterator for $data, for use with foreach
	 *
	 * @return ArrayIterator
	 */
	public function getIterator() {
		return(new ArrayIterator($this->data));
	}
}
?>
