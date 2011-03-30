Ext.ns('TYPO3.EnetcacheAnalytics');

Ext.onReady(function() {
		// Fire app
	var EnetcacheAnalytics = new TYPO3.EnetcacheAnalytics.App.init();
});

TYPO3.EnetcacheAnalytics.App = {
	init: function() {
		new Ext.TabPanel({
			renderTo: 'tx-enetcacheanalytics-mod-grid',
			activeTab: 1,
			plugins: [new Ext.ux.plugins.FitToParent()],
			items: [
				{
					title : 'Cache log analyzer',
					xtype: 'TYPO3.EnetcacheAnalytics.Analyze'
				},{
					title: 'Performance tests',
					xtype: 'TYPO3.EnetcacheAnalytics.Performance'
				}
			]
		});
	}
};