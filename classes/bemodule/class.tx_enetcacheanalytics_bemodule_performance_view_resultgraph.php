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
	 * @var array Raphael Javascript files
	 */
	protected static $javascriptFiles = array(
		'res/js/raphael/raphael.js',
		'res/js/raphael/g.raphael.js',
		'res/js/raphael/g.pie.js',
		'res/js/raphael/g.dot.js',
		'res/js/raphael/g.line.js',
	);

	/**
	 * Default constructor
	 */
	public function __construct($pObj) {
		$this->pObj = $pObj;
		$this->testStatistics = $pObj->getTestResults();
	}

	/**
	 * Render result table
	 *
	 * @return string HTML
	 */
	public function render() {
		$content = array();
		$content[] = $this->renderGraphs();
		return(implode(chr(10), $content));
	}

	protected function renderGraphs() {
		$this->pObj->setAdditionalJavascriptFiles(self::$javascriptFiles);
		$chartsDataArray = $this->getChartsDataArray();
//		debug($chartsDataArray);

		$chartJS = array();
		$chartCounter = 0;
		foreach ($chartsDataArray as $chartNumber => $chartData) {
			$chartJS[] = $this->getLineChartJS($chartNumber, $chartData);
			$chartCounter ++;
		}

		$chartMethodCallJS = '';
		$chartHTML = '';
		for ($i = 0; $i < $chartCounter; $i ++) {
			$chartMethodCallJS .= 'tx_enetcacheanalytics_chart' . $i . '();';
			$chartHTML .= '<div id="chart' . $i . '"></div>';
		}
		$content = '
			<script type="text/javascript" charset="utf-8">
				window.onload = function () {
					' . $chartMethodCallJS . '
				}
				' . implode(chr(10), $chartJS) . '
			</script>
			' . $chartHTML . '
		';
		return $content;
	}

	protected function getLineChartJS($chartNumber, $chartData) {
		$lines = array();
		foreach ($chartData['y'] as $lineNumber => $lineValues) {
			$lines[] = '[' . implode(',', $lineValues) . ']';
		}
		$y = '[' . implode(',', $lines) . ']';
		$content = '
			tx_enetcacheanalytics_chart' . $chartNumber . ' = function() {
				var r = Raphael("chart' . $chartNumber . '", 300, 300);

				r.g.txtattr.font = "12px \'Fontin Sans\', Fontin-Sans, sans-serif";
				r.g.text(110, 10, "' . $chartData['title'] . '");
				r.g.txtattr.font = "10px \'Fontin Sans\', Fontin-Sans, sans-serif";

				var x = [], y = [];
				x = [' . implode(',', $chartData['x']) . '];
				y = ' . $y . ';

				chart = r.g.linechart(20, 10, 200, 200, x, y, {
					nostroke: false,
					axis: "0 0 1 1",
					symbol: "o",
					axisxstep: 5,
				});
				chart.hoverColumn(function() {
					this.tags = r.set();
					for (var i = 0, ii = this.y.length; i < ii; i++) {
						this.tags.push(
							r.g.tag(this.x, this.y[i], this.values[i], 0, 8)
								.insertBefore(this).attr([{fill: "#fff"}, {fill: this.symbols[i].attr("fill")}]));
					}
				}, function () {
					this.tags && this.tags.remove();
				});

/*
				var i = 0;
				var labels = [\'' . implode(',', $chartData['labels']) . '\'];
				for each (var lab in chart.axis[0].text.items) {
					if (labels[i-1]) {
						lab.attr({"text": labels[i-1]})
					}
					i ++;
				}
*/
			}
		';
		return $content;
	}

	protected function getChartsDataArray() {
		$backendNames = array_keys($this->testStatistics);
		$aBackendName = current($backendNames);
		$testNames = array_keys(current($this->testStatistics));

			// Initialize data array
		$chartsDataArray = array();
		foreach ($testNames as $testName) {
			$chartsDataArray[] = array(
				'title' => $testName,
				'x' => array_keys($this->testStatistics[$aBackendName][$testName]),
				'y' => $backendNames,
				'labels' => $backendNames
			);
		}

			// Now calculate prime numbers :)
		$backendCounter = 0;
		foreach ($this->testStatistics as $backendTests) {
			$testcaseCounter = 0;
			foreach ($backendTests as $testcase) {
				$chartsDataArray[$testcaseCounter]['y'][$backendCounter] = array();
				foreach ($testcase as $testrunMessageList) {
					foreach ($testrunMessageList as $message) {
						if ($message instanceof tx_enetcacheanalytics_performance_message_TimeMessage) {
							$chartsDataArray[$testcaseCounter]['y'][$backendCounter][] = $this->pObj->formatTimeMessage($message['value']);
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
