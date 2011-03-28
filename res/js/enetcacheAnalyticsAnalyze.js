Ext.ns('TYPO3.EnetcacheAnalytics');

Ext.onReady(function() {
		// Fire app
	var EnetcacheAnalytics = new TYPO3.EnetcacheAnalytics.App.init();
});

TYPO3.EnetcacheAnalytics.App = {
	init: function() {
		Analyzer = new TYPO3.EnetcacheAnalytics.Analyze();
	}
}

TYPO3.EnetcacheAnalytics.Analyze = Ext.extend(Ext.grid.GridPanel, {
	layout: 'fit',
	renderTo: 'tx-enetcacheanalytics-mod-grid',
	border: false,
	defaults: {autoScroll: false},
	plain: true,
	plugins: [new Ext.ux.plugins.FitToParent()],

	initComponent:function() {
		TYPO3.EnetcacheAnalytics.Analyze.logEntryStore = new Ext.data.DirectStore({
			storeId: 'logEntry',
			idProperty: 'uid',
			directFn: TYPO3.EnetcacheAnalytics.Analyzer.getLogEntries,
			root: 'data',
			totalProperty: 'length',
			fields: [
				'uid', 'unique_id', 'page_uid', 'content_uid', 'be_user', 'fe_user',
				'tstamp', 'microtime',
				'request_type', 'caller', 'data', 'identifier', 'identifier_source', 'lifetime', 'tags'
			],
			paramsAsHash: true,
			paramNames: {
				unique_id: 'unique_id'
			}
		});

		this.logGroupStore = new Ext.data.DirectStore({
			storeId: 'logGroup',
			idProperty: 'unique_id',
			directFn: TYPO3.EnetcacheAnalytics.Analyzer.getLogGroups,
			root: 'data',
			totalProperty: 'length',
			fields: ['unique_id', 'title'],
			paramsAsHash: true
		});

		var cm = new Ext.grid.ColumnModel({
			columns: [
				{id: 'uid', header: 'uid', width: 30, dataIndex: 'uid'},
				{id: 'unique_id', header: 'Unique ID', width: 30, dataIndex: 'unique_id'}
			],
			defaults: {
				sortable: true,
				hideable: false
			}
		});

		Ext.apply(this, {
			store: TYPO3.EnetcacheAnalytics.Analyze.logEntryStore,
			cm: cm,
			tbar: [
				{
					xtype: 'tbtext',
					text: 'Log entry:'
				},
				TYPO3.EnetcacheAnalytics.logGroupCombo
			],
			viewConfig: {forceFit:true, scrollOffset:0}
		});

		TYPO3.EnetcacheAnalytics.Analyze.superclass.initComponent.apply(this, arguments);
	},

	onRender:function() {
		TYPO3.EnetcacheAnalytics.logGroupCombo.store = this.logGroupStore;
		TYPO3.EnetcacheAnalytics.logGroupCombo.on('select', function(comboBox, newValue, oldValue) {
			TYPO3.EnetcacheAnalytics.Analyze.logEntryStore.reload({ params: {unique_id: newValue.data.unique_id} });
		}, this);
		this.logGroupStore.load({
			callback: function() {
				if (this.getCount() == 0) {
//					TYPO3.Flashmessage.display(TYPO3.Severity.error, TYPO3.lang.msg_error, TYPO3.lang.repository_notfound, 15);
				} else {
					TYPO3.EnetcacheAnalytics.logGroupCombo.setValue(this.getAt(0).data.unique_id);
					TYPO3.EnetcacheAnalytics.Analyze.logEntryStore.reload({ params: {unique_id: this.getAt(0).data.unique_id} });
				}
			}
		});

		TYPO3.EnetcacheAnalytics.Analyze.superclass.onRender.apply(this, arguments);
	}
});

TYPO3.EnetcacheAnalytics.logGroupCombo = new Ext.form.ComboBox({
	id: 'logEntryCombo',
	mode: 'local',
	triggerAction: 'all',
	forceSelection: true,
	editable: false,
	name: 'selectedLogGroup',
	hiddenName: 'selectedLogGroup',
	displayField: 'title',
	valueField: 'unique_id',
	store: null,
	width: 200
});
