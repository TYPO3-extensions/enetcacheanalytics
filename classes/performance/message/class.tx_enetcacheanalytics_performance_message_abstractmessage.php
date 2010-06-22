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
 * Class 'AbstractMessage' for the 'enetcacheanalytics' extension.
 * Base class of all message types
 *
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 */
abstract class tx_enetcacheanalytics_performance_message_AbstractMessage extends tx_enetcacheanalytics_utility_Data implements tx_enetcacheanalytics_performance_message_Message {
	/**
	 * @var array Data array
	 */
	protected $data = array(
		'value' => integer,
		'message' => string,
	);
}
?>
