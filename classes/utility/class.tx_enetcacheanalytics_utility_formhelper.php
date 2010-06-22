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
 * Static helper methods to create common HTML form foo
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_utility_formhelper {
	/*
	 * @const string Form namespace prefix
	 */
	const prefix = 'tx_enetcacheanalytics';

	/**
	 * Create a simple submit button
	 *
	 * @param string title Button title
	 * @param string action Next step action
	 * @return string submit button HTML
	 */
	public static function makeSubmitButton($title, $action) {
		return '<input type="submit" onClick="setAction(\'' . $action . '\');" value="' . $title . '" />';
	}

	/**
	 * Create a simple text input
	 *
	 * @param string field name
	 * @param integer size
	 * @param string default value
	 * @return string input text HTML
	 */
	public static function makeTextInput($name, $size = 10, $default = '') {
		return '<input type="text" value="' . $default . '" name="' . self::wrap($name) . '" id="' . $name . '" size = "' . $size . '" />';
	}

	/**
	 * Create a file upload field
	 *
	 * @param string field name
	 * @return string input file HTML
	 */
	public static function makeUploadField($name) {
		return '<input type="file" name="' . self::wrap($name) . '" size="50" />';
	}

	/**
	 * Create a hidden field
	 *
	 * @param string field name
	 * @param string value
	 * @param string field subsubarray name (please refactor me ...)
	 * @return string HTML of hidden field
	 */
	public static function makeHiddenField($name, $value, $subname = '', $subsubname = '') {
		return '<input type="hidden" name="' .
			self::wrap($name) .
			(strlen($subname) ? '[' . $subname . ']' : '') .
			(strlen($subsubname) ? '[' . $subsubname . ']' : '') .
			'" ' .
			'value="' .	$value . '" />';
	}

	/**
	 * Create a checkbox
	 *
	 * @param string field name
	 * @param string value
	 * @param boolean TRUE if checked
	 * @param string subname
	 * @return string HTML of checkbox
	 */
	public static function makeCheckbox($name, $value, $checked = FALSE, $subname = '') {
		return '<input type="checkbox" name="' .
			self::wrap($name) .
			(strlen($subname) ? '[' . $subname . ']" ' : '" ') .
			'value="' . $value . '" ' .
			($checked ? 'checked="checked" ' : ' ') .
			'/>';
	}

	/**
	 * Create a selector box
	 *
	 * @param string field name
	 * @param array option array: text, value, flag if selected
	 * @param string field subarray name
	 * @param string Additonal text for select form element
	 * @param string field subsubarray name (please refactor me ...)
	 */
	public static function makeSelectBox($name, $options, $subname = '', $additionalAttributes = '', $subsubname = '') {
		$content = array();
		$content[] = '<select name="' .	self::wrap($name) .
			(strlen($subname) ? '[' . $subname . ']' : '') .
			(strlen($subsubname) ? '[' . $subsubname . ']' : '') .
			'" ' .
			$additionalAttributes . ' ' .
			'>';
		foreach ($options as $optionNumber => $option) {
			$selected = $option[2] ? ' selected="selected"' : '';
			$content[] = '<option' . $selected . ' value="' . $option[1] . '">' . $option[0] . '</option>';
		}
		$content[] = '</select>';
		return implode(chr(10), $content);
	}

	/**
	 * Wrap a form name in extension prefix
	 *
	 * @param string name
	 * @return string wrapped name
	 */
	public static function wrap($name) {
		return 'DATA[' . self::prefix . '_' . $name . ']';
	}
}
