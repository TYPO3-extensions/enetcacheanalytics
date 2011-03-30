Ext.ns('TYPO3.EnetcacheAnalytics');

TYPO3.EnetcacheAnalytics.Performance = Ext.extend(Ext.Panel, {
	layout: 'border',

	initComponent:function() {

		Ext.apply(this, {
			items: [
				{
					region: 'west',
					layout: 'fit',
					frame: true,
					border: false,
					width: 400,
					split: true,
					collapsible: true,
					collapseMode: 'mini'
				},
				{
					region: 'center',
					layout: 'fit',
					frame: true,
					border: false
				}
			]
		});

		TYPO3.EnetcacheAnalytics.Performance.superclass.initComponent.apply(this, arguments);
	},

	onRender:function() {
		TYPO3.EnetcacheAnalytics.Performance.superclass.onRender.apply(this, arguments);
	}
});

Ext.reg('TYPO3.EnetcacheAnalytics.Performance', TYPO3.EnetcacheAnalytics.Performance);