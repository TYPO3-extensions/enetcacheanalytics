Ext.ns('TYPO3.EnetcacheAnalytics');

Ext.onReady(function() {
		// Save stateful components in BE_USER->uc moduleData.tools_enetcacheanalytics.States
	Ext.state.Manager.setProvider(new TYPO3.state.ExtDirectProvider({
		key: 'moduleData.tools_enetcacheanalytics.States',
		autoRead: false
	}));

		// Initial states in TYPO3.settings.enetcacheAnalytics are
		// provided in page source by php addInlineSettingArray
	if (Ext.isObject(TYPO3.settings.enetcacheAnalytics.States)) {
		Ext.state.Manager.getProvider().initState(TYPO3.settings.enetcacheAnalytics.States);
	}

		// Fire app
	var EnetcacheAnalytics = new TYPO3.EnetcacheAnalytics.App.init();
});

TYPO3.EnetcacheAnalytics.App = {
	init: function() {
		new Ext.TabPanel({
			renderTo: 'tx-enetcacheanalytics-mod-grid',
			activeTab: 0,
			stateful: true,
			stateId: 'mainTab',
			stateEvents:['tabchange'],
			plugins: [new Ext.ux.plugins.FitToParent()],
			items: [
				{
					title : 'Cache log analyzer',
					xtype: 'TYPO3.EnetcacheAnalytics.Analyze'
				},{
					title: 'Performance tests',
					xtype: 'TYPO3.EnetcacheAnalytics.Performance'
				}
			],
			getState: function() {
				return {
					activeTab: this.items.indexOf(this.getActiveTab())
                };
			}
		});
	}
};