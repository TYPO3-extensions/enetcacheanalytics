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
 * Class 'list' for the 'enetcacheanalytics' extension.
 * Base class of all list objects
 * Implements php SPL Iterator to iterate over list objects with foreach
 * Implements php SPL Countable to return number of list objects with count
 *
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 */
abstract class tx_enetcacheanalytics_utility_List implements Iterator, Countable {
	/**
	 * @var array List elements
	 */
	protected $list = array();

	/**
	 * @var bool Switch to keep track of the end of the element array
	 */
	protected $valid = FALSE;

	/**
	 * Default constructor
	 *
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * Defined by Iterator Interface
	 * Reset the list pointer to the first element
	 * PHP's reset() returns false if the array has no elements
	 *
	 * @return void
	 */
	public function rewind() {
		$this->valid = (FALSE !== reset($this->list));
	}

	/**
	 * Defined by Iterator Interface
	 * Return the current list element
	 *
	 * @return mixed Element
	 */
	public function current() {
		return current($this->list);
	}

	/**
	 * Defined by Iterator Interface
	 * Return the key of the current list element
	 *
	 * @return integer Current element key
	 */
	public function key() {
		return key($this->list);
	}

	/**
	 * Defined by Iterator Interface
	 * Move pointer to next list element
	 * PHP's next() returns false if there are no more elements
	 *
	 * @return void
	 */
	public function next() {
		$this->valid = (FALSE !== next($this->list));
	}

	/**
	 * Defined by Iterator Interface
	 * Is the current pointer to the list element array valid?
	 *
	 * @return void
	 */
	public function valid() {
		return $this->valid;
	}

	/**
	 * Defined by Countable Interface
	 * Returns the number of list items
	 *
	 * @return integer Number of list items
	 */
	public function count() {
		return count($this->list);
	}

	/**
	 * Add item to the list
	 *
	 * @param object item
	 * @return void
	 */
	public function add(tx_enetcacheanalytics_utility_Data $item) {
		$this->list[] = $item;
	}
}
?>
