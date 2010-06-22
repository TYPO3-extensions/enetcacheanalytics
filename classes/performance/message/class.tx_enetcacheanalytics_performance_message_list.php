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
 * Class 'List' for the 'enetcacheanalytics' extension.
 *
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 */
class tx_enetcacheanalytics_performance_message_List extends tx_enetcacheanalytics_utility_List {
	/**
	 * Possible message types
	 */
	protected static $messageTypes = array(
		'TimeMessage',
		'OperationCountMessage',
		'CacheMissMessage',
	);

	/**
	 * Add item to the list
	 *
	 * @param object item
	 * @return void
	 */
	public function add(tx_enetcacheanalytics_performance_message_Message $message) {
		parent::add($message);
	}

	/**
	 * Return list of possible message type objects
	 *
	 * @return array List of short class names
	 */
	public static function getPossibleMessageTypes() {
		return self::$messageTypes;
	}
}
?>
