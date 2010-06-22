<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2010 Christian Kuhn <lolli@schwarzbu.ch>
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
 * Function 'Overview' for the 'enetcacheanalytics' BE module.
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Michael Knabe <mk@e-netconsulting.de>
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_bemodule_cacheanalyzer implements tx_enetcacheanalytics_bemodule {
	/**
	 * @var tx_enetcacheanalytics_module1 Default parent object
	 */
	protected $pObj = object;

	/**
	 * @var array Global get/post vars
	*/
	protected $GPvars = array();

	/**
	 * @var array Helper with previous row to determine connection of get / set system
	 */
	protected $previousRow = array();

	/**
	 * Counter and stat variables
	 */
	protected $uniqueID = 0;
	protected $timestamp = 0;
	protected $piCounter = 0;
	protected $completeTime = 0;
	protected $cacheTime = 0;
	protected $cacheCounter = 0;
	protected $cacheCounterSuccessful = 0;


	/**
	 * Default init method, required by interface
	 *
	 * @return void
	 */
	public function init(tx_enetcacheanalytics_module1 &$pObj) {
		$this->pObj = $pObj;
		$this->GPvars = $pObj->getGPvars();
	}


	/**
	 * Default execute, required by interface
	 *
	 * @return void
	 */
	public function execute() {
			// Handle actions triggered by GPvars
		$this->handleAction();

			// Add additional JS to parent object
		$this->pObj->setAdditionalJavascript($this->additionalJavascript());

			// Main content
		$this->pObj->setContentMarker($this->renderMainModuleContent());

			// Additinal functions in doc header
		$this->pObj->setAdditionalDocHeaderMarker($this->renderDocHeaderOptions());
	}


	/**
	 * Handle module specific actions:
	 * - Removal of specic cache entries (page or page + content element)
	 *
	 * @return void
	 */
	protected function handleAction() {
		if (!isset($this->GPvars['tx_enetcacheanalytics_action'])) {
			return;
		}

		switch ($this->GPvars['tx_enetcacheanalytics_action']) {
			case 'dropTag':
				if (t3lib_extMgm::isLoaded('enetcache')) {
					t3lib_div::makeInstance('tx_enetcache')->drop(array($this->GPvars['tx_enetcacheanalytics_identifier']));
				}
			break;
			case 'dropPageCache':
				$pageCache = $GLOBALS['typo3CacheManager']->getCache('cache_pages');
				$pageCache->flushByTag('pageId_' . intval($this->GPvars['tx_enetcacheanalytics_flushPageId']));
			break;
		}
	}


	/**
	 * Add additional Javascript to document
	 *
	 * @return string Additional JS
	 */
	protected function additionalJavascript() {
		$javascript = array();
		$javascript[] = 'var imageExpand = \'<img' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/plusbullet_list.gif', 'width="18" height="12"') . ' alt="+" />\';';
		$javascript[] = 'var imageCollapse = \'<img' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/minusbullet_list.gif', 'width="18" height="12"') . ' alt="-" />\';';
		$javascript[] = '
			function toggleExtraData(theID) {
				var theLink = $(\'debug-link-\' + theID);
				var theElement = $(\'debug-row-\' + theID);
				if (theElement.visible()) {
					theElement.hide();
					theLink.update(imageExpand);
					theLink.title = \''.$GLOBALS['LANG']->getLL('show_extra_data') . '\';
				}
				else {
					theElement.show();
					theLink.update(imageCollapse);
					theLink.title = \''.$GLOBALS['LANG']->getLL('hide_extra_data') . '\';
				}
			}
		';
		return implode(chr(10), $javascript);
	}


	/**
	 * Render additional drop downs and actions in document header
	 *
	 * @return string select box HTML
	 */
	protected function renderDocHeaderOptions() {
		$content = array();
		$content[] = $this->renderRequestIdSelector();
		$content[] = $this->renderRequestPageIdSelector();
		$content[] = $this->renderRefreshButton();

		return (implode(chr(10), $content));
	}


	/**
	 * Render a drop down to choose a specific log entry
	 *
	 * @return string HTML
	 */
	protected function renderRequestIdSelector() {
		$wherePage = '1 ';
		if (strlen($this->GPvars['tx_enetcacheanalytics_pageId']) > 0) {
			$wherePage .= 'AND page_uid='. intval($this->GPvars['tx_enetcacheanalytics_pageId']);
		}

		$possibleRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT unique_id, tstamp, page_uid',
			'tx_enetcache_log',
			$wherePage,
			'',
			'tstamp DESC'
		);

		$requestSelectOptions = array();
		$selected = '';
		if (!isset($this->GPvars['tx_enetcacheanalytics_requestId'])) {
			$selected = ' selected="selected" ';
		}
		$requestSelectOptions[] = '<option value=""' . $selected . '>Youngest log entry</option>';

		foreach ($possibleRows as $request) {
			$selected = ($request['unique_id'] == $this->GPvars['tx_enetcacheanalytics_requestId']) ? ' selected="selected"' : '';
			$formatDate = date('Y-m-d H:i:s', $request['tstamp']);
			$formatPageID = ($request['page_uid'] > 0) ? 'pid:' . $request['page_uid'] : '';
			$value = $formatDate . ' ' . $formatPageID;
			$requestSelectOptions[] = '<option value="' . $request['unique_id'] . '" ' . $selected . '>' . $value . '</option>';
		}

		$requestIdSelectContent = '
			<select name="DATA[tx_enetcacheanalytics_requestId]" onchange="setFieldValue(\'requestId\', \'new\'); document.enetcacheanalytics.submit();">
				' . implode(chr(10), $requestSelectOptions) . '
			</select>
			';

		return $requestIdSelectContent;
	}


	/**
	 * Render a drop down to reduce the former selector to specific page ID's
	 *
	 * @return string HTML
	 */
	protected function renderRequestPageIdSelector() {
		$pages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT page_uid',
			'tx_enetcache_log',
			'1',
			'',
			'page_uid ASC'
		);

		$pageSelectOptions = array();
		$selected = '';
		$selectedPageID = intval($this->GPvars['tx_enetcacheanalytics_pageId']);
		if (strlen($this->GPvars['tx_enetcacheanalytics_pageId']) == 0) {
			$selected = ' selected="selected" ';
		}
		$pageSelectOptions[] = '<option value=""' . $selected . '>Show only page id</option>';

		foreach ($pages as $page) {
			$selected = '';
			if ($page['page_uid'] == $selectedPageID && strlen($this->GPvars['tx_enetcacheanalytics_pageId']) > 0) {
				$selected = ' selected="selected" ';
			}
			$pageSelectOptions[] = '<option value="' . $page['page_uid'] . '" ' . $selected . '>Page ' . $page['page_uid'] . '</option>';
		}

		$pageSelectContent = '
			<select name="DATA[tx_enetcacheanalytics_pageId]" onchange="setFieldValue(\'pageId\', \'new\'); document.enetcacheanalytics.submit();">
				' . implode(chr(10), $pageSelectOptions) . '
			</select>
		';

		return($pageSelectContent);
	}


	/**
	 * Render a refresh button to document header
	 *
	 * @return string HTML
	 */
	protected function renderRefreshButton() {
		return '<input type="submit" value="Refresh" />';
	}


	/**
	 * @return string HTML of log table
	 */
	protected function renderMainModuleContent() {
		$this->uniqueID = $this->determineRequestID();
		$logRows = $this->getLogRows($this->uniqueID);

		$content = array();
		$content[] = $this->renderLogTable($logRows);
		array_unshift($content, $this->renderSummary());

		return(implode(chr(10), $content));
	}


	/**
	 * @return string unique_id of request
	 */
	protected function determineRequestID() {
		$wherePage = '1 ';
		if (strlen($this->GPvars['tx_enetcacheanalytics_pageId']) > 0) {
			$wherePage .= 'AND page_uid='. intval($this->GPvars['tx_enetcacheanalytics_pageId']);
		}

		if (strlen($this->GPvars['tx_enetcacheanalytics_requestId']) > 0) {
			return($this->GPvars['tx_enetcacheanalytics_requestId']);
		} else {
				// Get latest request id
			$request_id = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'unique_id',
				'tx_enetcache_log',
				$wherePage,
				'',
				'tstamp DESC',
				'1'
			);
			return($request_id[0]['unique_id']);
		}
	}


	protected function getLogRows($uniqueID) {
		$logRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tx_enetcache_log',
			'unique_id = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($uniqueID, 'tx_enetcache_log'),
			'',
			'uid ASC'
		);

		return $logRows;
	}


	protected function renderLogTable($logRows) {
		$content = array();
		$content[] = '<table>';
		$content[] = $this->renderLogTableHeader();
		$content[] = $this->renderLogTableRows($logRows);
		$content[] = '</table>';

		return implode(chr(10), $content);
	}


	protected function renderSummary() {
		$result = sprintf('
			<div><b>%s</b>: Display request with unique ID %s.<br />
			<div><b>%s</b> of <b>%s</b> rendered plugins used enetcache (<b>%1.2f%%</b>).<br />
			<div><b>%s</b> of <b>%s</b> enetcache get requests where successful (<b>%1.2f%%</b>).<br />
			Could have saved <b>%s</b>ms of <b>%s</b>ms (<b>%1.2f%%</b>).</div><br />',
			date('Y-m-d H:i:s', $this->timestamp),
			$this->uniqueID,
			$this->cacheCounter,
			$this->piCounter,
			$this->cacheCounter / max($this->piCounter, 0.00001) * 100,
			$this->cacheCounterSuccessful,
			$this->cacheCounter,
			$this->cacheCounterSuccessful / max($this->cacheCounter, 0.00001) * 100,
			$this->cacheTime,
			$this->completeTime,
			$this->cacheTime / max($this->completeTime, 0.00001) * 100
		);

		return $result;
	}


	protected function renderLogTableHeader() {
		$headerTitles = array(
			'PID',
			'UID',
			'FE&nbsp;-&nbsp;BE<br />User',
			'Life time /<br />Valid until',
			'Caller',
			'Identifier',
			'Tags',
			'Data',
			'Time spent',
		);

		$headerTH = array();
		foreach($headerTitles as $title) {
			$headerTH[] = '<th>' . $title . '</th>';
		}

		$content = '
			<tr class="head">
				' . implode(chr(10), $headerTH) . '
			</tr>
		';

		return $content;
	}


	/**
	 * Render table rows
	 *
	 * @return string HTML Table rows
	 */
	protected function renderLogTableRows($rows) {
		$style = ' style="display: none;"';
		$icon = '<img' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/plusbullet_list.gif','width="18" height="12"') . ' alt="+" />';
		foreach ($rows as $row) {
			$this->logStats($row);

			$tags = unserialize($row['tags']);

			$result = '';
			$cssClass = strtolower($row['request_type']);
			if(strtolower($row['request_type']) == 'get') {
				$cssClass = ($row['data']==='FALSE' ? 'fail' : 'success');
			}

			if ($row['request_type'] === 'USER' && intval($row['page_uid']) > 0) {
				$dropPageCacheIcon = '<img style="cursor: pointer;" ' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/garbage.gif') . ' onclick="setAction(\'dropPageCache\'); setFieldValue(\'flushPageId\', \'' . $row['page_uid'] . '\'); document.enetcacheanalytics.submit();" alt="Drop cache entry of this page (not cache of content elements)" title="Drop cache entry of this page (not cache of content elements)" />';
				$result .= $this->renderLogTableTD($row['page_uid'] . '<br />' . $dropPageCacheIcon, '');
			} elseif ($row['request_type'] != 'USER' XOR $row['page_uid'] == 0) {
				$result .= $this->renderLogTableTD($row['page_uid'], 'visibility: hidden;');
			} else {
				$result .= $this->renderLogTableTD($row['page_uid'], '');
			}

			if (t3lib_extMgm::isLoaded('enetcache')) {
				t3lib_div::makeInstance('tx_enetcache');
			}
			if (( $row['request_type'] === 'GET' || $row['request_type'] === 'SET' ) && 
					$GLOBALS['typo3CacheManager']->getCache('cache_enetcache_contentcache')->get($row['identifier'])) {
				$dropTagIcon = '<img style="cursor: pointer;" ' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/garbage.gif') . ' onclick="setAction(\'dropTag\'); setFieldValue(\'identifier\', \'' . $row['identifier'] . '\'); document.enetcacheanalytics.submit();" alt="Drop cache entry (content element and page) with this identifier" title="Drop cache entry (content element and page) with this identifier" />';
				$result .= $this->renderLogTableTD($dropTagIcon);
			} elseif ($row['request_type'] != 'USER' XOR $row['page_uid'] == 0) {
				$result .= $this->renderLogTableTD($row['content_uid'], 'visibility: hidden;');
			} else {
				$result .= $this->renderLogTableTD($row['content_uid']);
			}

			$result .= $this->renderLogTableTD($row['fe_user'] . ' - ' . $row['be_user']);

			if ($row['request_type'] === 'SET') {
				$row['lifetime'] = date('Y-m-d H:i:s', (time() + $row['lifetime'])) . '<br />= +' . $row['lifetime'];
				$result .= $this->renderLogTableTD($row['lifetime']);
			} elseif (is_array($tags) && ($row['request_type'] === 'GET')) {
				$row['endtime'] = date('Y-m-d H:i:s', $tags['endtime']) . '<br />= +' . ($tags['endtime'] - time());
				$result .= $this->renderLogTableTD($row['endtime']);
			} else {
				$result .= $this->renderLogTableTD('');
			}

			$result .= $this->renderLogTableTD($this->unserializeCallerField($row['caller']));
			if ($row['request_type']!='USER') {
				$result .= $this->renderLogTableTD(
					$row['identifier'] .
					'<br />'.
					'<a href="javascript:toggleExtraData(\'identifier' . $row['uid'] . '\')" id="debug-link-identifier' . $row['uid'] . '" title="' . $label . '">' .
					$icon .
					'</a>'.
					'<div id="debug-row-identifier' . $row['uid'] . '"' . $style . '>' . t3lib_div::view_array(unserialize($row['identifier_source'])) . '</div>'
				);
			} else {
				$result .= $this->renderLogTableTD('');
			}

			if(is_array($tags)) {
				if($row['request_type']!='GET') {
					$row['tags'] = implode('<br />',$tags);
				} else {
					$row['tags'] = implode('<br />',$tags['tags']);
				}
			}
			$result .= $this->renderLogTableTD($row['tags']);

			if ($row['request_type']!='USER' && $row['data']!=='FALSE') {
				$result .= $this->renderLogTableTD(
					'<br />'.
					'<a href="javascript:toggleExtraData(\'data' . $row['uid'] . '\')" id="debug-link-data' . $row['uid'] . '" title="' . $label . '">' .
					$icon .
					'</a>'.
					'<div id="debug-row-data' . $row['uid'] . '"' . $style . '>' . t3lib_div::view_array(unserialize($row['data'])) . '</div>'
				);
			} else {
				$result .= $this->renderLogTableTD('');
			}
			
			$diff = '';
			if($this->lastRow['request_type']=='GET' && $row['request_type']=='SET'
				&& $this->lastRow['identifier'] == $row['identifier'] 
			) {
				$diff =  ($row['microtime'] - $this->lastRow['microtime']);
			} else if($row['request_type']=='USER') {
				$diff =  $row['data'];
			}
			
			$result .= $this->renderLogTableTD($diff);
			$content .= '
				<tr class="' . $cssClass . '" ' . (($row['request_type']=='USER') ? 'style="border-top: 2px solid black;"' : '') . '>
					' . $result . '
				</tr>
			';
			if($row['request_type']=='SET' || $row['request_type']=='GET') {
				$this->lastRow = $row;
			}
		}

		return($content);
	}


	protected function logStats($row) {
		if ($this->timestamp == 0) {
			$this->timestamp = $row['tstamp'];
		}
		switch ($row['request_type']) {
			case 'USER':
				$this->piCounter ++;
				$this->completeTime += $row['data'];
			break;
			case 'GET':
				$this->cacheCounter ++;
				if ($row['data']!=='FALSE') {
					$this->cacheCounterSuccessful ++;
				}
			break;
			case 'SET':
				if($this->lastRow['identifier'] == $row['identifier']) {
					$this->cacheTime += ($row['microtime'] - $this->lastRow['microtime']);
				}
		}
	}


	protected function renderLogTableTD($value, $style='') {
		if($style) {
			$style = 'style="' . $style . '"';
		}
		$result = array();
		$result[] = '<td ' . $style . '>';
		$result[] = $value;
		$result[] = '</td>';

		return implode(chr(10), $result);
	}


	protected function unserializeCallerField($callerField) {
		$callerField = unserialize($callerField);

		$result = array();
		foreach ($callerField as $k => $v) {
			$result[] = $k . ': ' . $v . '<br />';
		}
		
		return implode(chr(10), $result);
	}
} // end of class
?>
