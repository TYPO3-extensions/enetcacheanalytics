<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Christian Kuhn <lolli@schwarzbu.ch>
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
 * Class 'Performance graph result' for the 'enetcacheanalytics' BE module.
 * Render graphs fropm given test results of performance module
 *
 * @package TYPO3
 * @subpackage tx_enetcacheanalytics
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class tx_enetcacheanalytics_bemodule_performance_view_ResultGraph {
	/**
	 * @var tx_enetcacheanalytics_bemodule Default parent object
	 */
	protected $pObj = object;

	/**
	 * @var array Gathered output of performance tests
	 */
	protected $testStatistics = array();

	/**
	 * Graph parameters
	 */
	protected static $graphWidth = 1000;
	protected static $graphHeight = 350;

	/**
	 * Default constructor
	 */
	public function __construct($pObj) {
		$this->pObj = $pObj;
		$this->testStatistics = $pObj->getTestResults();

			// Add ezcomponents autoloader
		require_once(t3lib_extMgm::extPath('enetcacheanalytics') . 'res/ezcomponents/Base/src/base.php');
		spl_autoload_register(array('ezcBase', 'autoload'));
	}

	/**
	 * Render result table
	 *
	 * @return string HTML
	 */
	public function render() {
		$content = array();
		$content[] = $this->renderGraphs();
		return implode(chr(10), $content);
	}

	protected function renderGraphs() {
		$chartsDataArray = $this->convertChartsDataArray();
		$content = array();
		foreach ($chartsDataArray as $number => $chartsData) {
			$content[] = $this->renderChart($chartsData);
		}
		return implode(chr(10), $content);
	}

	/**
	 * Render a chart with ezComponents Graph Component
	 *
	 * @param array Test values and title
	 * @return string HTML of a chart
	 */
	protected function renderChart($data) {
		$graph = new ezcGraphLineChart();
		$graph->palette = new ezcGraphPaletteBlack();

		$graph->title->font->maxFontSize = 16;
		$graph->title->font->name = 'sans-serif';
		$graph->title->background = '#000000';
		$graph->title = $data['title'];
		$graph->title->borderWidth = 0;
		$graph->title->padding = 2;
		$graph->title->margin = 0;

		$graph->legend->position = ezcGraph::LEFT;
		$graph->legend->background = '#000000';
		$graph->legend->font->name = 'sans-serif';
		$graph->legend->borderWidth = 0;
		$graph->legend->padding = 2;
		$graph->legend->margin = 0;
		$graph->legend->symbolSize = 10;

		$graph->options->font->maxFontSize = 12;
		$graph->options->font->name =  'serif';
		$graph->options->highlightSize = 9;

		$graph->yAxis = new ezcGraphChartElementNumericAxis();
		$graph->yAxis->min = 0;
		$graph->yAxis->label = 'Seconds';

		$graph->xAxis = new ezcGraphChartElementNumericAxis();
		$graph->xAxis->min = 0;

		foreach ($data['xy'] as $backendName => $backendValues) {
			$graph->data[$backendName] = new ezcGraphArrayDataSet($backendValues);
			$graph->data[$backendName]->displayType = ezcGraph::LINE;
			$graph->data[$backendName]->highlight = TRUE;
		}

			// Render graph content to buffer and fill to local variable
		ob_start();
		$graph->render(self::$graphWidth, self::$graphHeight, 'php://output');
		$chartContent = ob_get_contents();
		ob_end_clean();

			// Stuff content to local tempfile and create HTML foo
		$filename = t3lib_div::shortMD5($chartContent) . '.svg';
		t3lib_div::writeFileToTypo3tempDir(PATH_site . 'typo3temp/' . 'tx_enetcacheanalytics/' . $filename, $chartContent);
		return '<div style="margin-bottom: 3px;"><object data="../typo3temp/tx_enetcacheanalytics/' . $filename .'" width="' . self::$graphWidth . '" height="' . self::$graphHeight . '" /></div>';
	}

	/**
	 * Convert test array with data to usable charts data
	 *
	 * @return array Testcase array with Title, backendName and TestValues
	 */
	protected function convertChartsDataArray() {
		$testNames = array_keys(current($this->testStatistics));

			// Initialize data array
		$chartsDataArray = array();
		foreach ($testNames as $testName) {
			$chartsDataArray[] = array(
				'title' => $testName,
				'xy' => array(),
			);
		}

			// Now calculate prime numbers :)
		$backendCounter = 0;
		foreach ($this->testStatistics as $backendName => $backendTests) {
			$testcaseCounter = 0;
			foreach ($backendTests as $testcase) {
				$chartsDataArray[$testcaseCounter]['xy'][$backendName] = array();
					// Add a dummy 0 / 0 value
				$chartsDataArray[$testcaseCounter]['xy'][$backendName][0] = 0;
				foreach ($testcase as $testrunVariable => $testrunMessageList) {
					foreach ($testrunMessageList as $message) {
						if ($message instanceof tx_enetcacheanalytics_performance_message_TimeMessage) {
							$chartsDataArray[$testcaseCounter]['xy'][$backendName][$testrunVariable] = $this->pObj->formatTimeMessage($message['value']);
						}
					}
				}
				$testcaseCounter ++;
			}
			$backendCounter ++;
		}

		return $chartsDataArray;
	}
} // end of class
?>
