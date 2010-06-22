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
 * Abstract test implementation class
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
abstract class tx_enetcacheanalytics_performance_testcase_AbstractTestcase implements tx_enetcacheanalytics_performance_testcase_Testcase {
	/**
	 * @var string Testcase name
	 */
	protected $name = '';

	/**
	 * @var t3lib_cache_backend_Backend Instance of the cache backend
	 */
	protected $backend;

	/**
	 * Default constructor initializes test case name
	 */
	public function __construct() {
		$this->name = str_replace('tx_enetcacheanalytics_performance_testcase_', '', get_class($this));
	}

	/**
	 * Set up this test case
	 *
	 * @var tx_enetcacheanalytics_performance_backend_Backend Backend instance to run test on
	 * @return void
	 */
	public function setUp(tx_enetcacheanalytics_performance_backend_Backend $backend) {
		$this->backend = $backend;
	}

	/**
	 * Tear down / clean up test data
	 *
	 * @return void
	 */
	public function tearDown() {
		$this->backend->flush();
	}

	/**
	 * Get testcase name
	 *
	 * @return string Name
	 */
	public function getName() {
		return $this->name;
	}
} // end of class
?>
