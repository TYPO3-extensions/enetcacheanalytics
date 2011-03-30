Ext.ns('TYPO3.EnetcacheAnalytics');


Ext.onReady(function() {
		// Fire app
	var EnetcacheAnalytics = new TYPO3.EnetcacheAnalytics.App.init();
});


TYPO3.EnetcacheAnalytics.App = {
	init: function() {
		new Ext.TabPanel({
			renderTo: 'tx-enetcacheanalytics-mod-grid',
			activeTab: 0,
			plugins: [new Ext.ux.plugins.FitToParent()],
			items: [
				{
					title : 'Cache log analyzer',
					xtype: 'TYPO3.EnetcacheAnalytics.Analyze'
				},{
					title: 'Performance tests',
					html: 'foo'
				}
			]
		});
	}
};


TYPO3.EnetcacheAnalytics.Analyze = Ext.extend(Ext.grid.GridPanel, {
	layout: 'fit',
	border: false,
	defaults: {autoScroll: false},
	plain: true,

	expander: new Ext.ux.grid.RowPanelExpander({
		id: 'LogRowListExpander',
		createExpandingRowPanelItems: function(record, rowIndex) {
			var panelItems = [
				new Ext.TabPanel({
					plain: true,
					activeTab: 0,
					defaults: {
						autoHeight: true
					},
					record: record,
					items:[
						{
							title: 'Identifier',
							html: record.data.identifier
						},
						{
							title: 'Identifier Array',
							html: record.data.identifier_source
						},
						{
							title: 'Data',
							html: record.data.data
						}
					]
				})
			];
			return panelItems;
		},

		getRowClass: function(record, rowIndex, p, ds) {
			var cssClass = '';
			var type = record.get('request_type');
			switch (type) {
				case 'GET':
					if (record.get('data') == '') {
						cssClass = 'cache-get-failed';
					} else {
						cssClass = 'cache-get-successful';
					}
					break;
				case 'SET':
					cssClass = 'cache-set';
					break;
			}

			p.cols = p.cols - 1;
			var content = this.bodyContent[record.id];
			if (!content && !this.lazyRender) {
				content = this.getBodyContent(record, rowIndex);
			}
			if (content) {
				p.body = content;
			}
			if (this.state[record.id]) {
				cssClass = cssClass + ' x-grid3-row-expanded';
			} else {
				cssClass = cssClass + ' x-grid3-row-collapsed';
			}
			
			return cssClass;
		},

		renderer : function(v, p, record) {
			if (record.data.identifier.length > 0 || record.data.data.length > 0 || record.data.identifier_source.length > 0) {
				p.cellAttr = 'rowspan="2"';
				var expanderHtml = '<div class="x-grid3-row-expander">&#160;</div>';
			} else {
				var expanderHtml = '';
			}
			return expanderHtml;
		}
	}),

	initComponent:function() {
		TYPO3.EnetcacheAnalytics.Analyze.logEntryStore = new Ext.data.DirectStore({
			storeId: 'logEntry',
			idProperty: 'uid',
			directFn: TYPO3.EnetcacheAnalytics.Analyzer.getLogEntries,
			root: 'data',
			totalProperty: 'length',
			fields: [
				'uid', 'unique_id', 'page_uid', 'content_uid', 'user',
				'time',
				'request_type', 'caller', 'data', 'identifier', 'identifier_source', 'lifetime', 'tags'
			],
			paramsAsHash: true,
			paramNames: {
				unique_id: 'unique_id'
			}
		});

		TYPO3.EnetcacheAnalytics.Analyze.logStatsStore = new Ext.data.DirectStore({
			storeId: 'logStats',
			idProperty: 'unique_id',
			directFn: TYPO3.EnetcacheAnalytics.Analyzer.getLogStats,
			root: 'data',
			totalProperty: 'length',
			fields: [
				'unique_id', 'numberOfPlugins', 'numberOfEnetcachePlugins', 'numberOfSuccessfulGets'
			],
			paramsAsHash: true,
			paramNames: {
				unique_id: 'unique_id'
			},
			listeners: {
				load: function(store, records) {
					Ext.getCmp('logGroupInfo').update(TYPO3.EnetcacheAnalytics.Layouts.logStats().applyTemplate(records[0]['data']));
				},
				scope: this
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
				this.expander,
				{id: 'page_uid', header: 'PID', dataIndex: 'page_uid', width: 16},
				{id: 'content_uid', header: 'UID', dataIndex: 'content_uid', width: 16},
				{id: 'request_type', header: 'Type', dataIndex: 'request_type', width: 30},
				{id: 'time', header: 'Time', dataIndex: 'time', width: 16},
				{id: 'user', header: 'User', dataIndex: 'user', width: 20},
				{id: 'caller', header: 'Caller', dataIndex: 'caller', width: 200},
				{id: 'tags', header: 'Tags', dataIndex: 'tags'}
			],
			defaults: {
				sortable: false,
				menuDisabled: true,
				hideable: false
			}
		});

		Ext.apply(this, {
			store: TYPO3.EnetcacheAnalytics.Analyze.logEntryStore,
			plugins: [this.expander],
			cm: cm,
			tbar: [
				{
					xtype: 'tbtext',
					text: 'Log entry:'
				},
				TYPO3.EnetcacheAnalytics.logGroupCombo,
				new Ext.Button({
            		tooltip: 'Refresh',
					tooltipType: 'title',
            		iconCls: 'x-tbar-loading',
					scope: this,
            		handler: function() {
						TYPO3.EnetcacheAnalytics.logGroupCombo.store.reload();
					}
        		}),
				'-',
				{
					xtype: 'container',
					id: 'logGroupInfo',
					html: ''
				}
			],
			viewConfig: {
				forceFit: true,
				scrollOffset: 0
			}
		});

		TYPO3.EnetcacheAnalytics.Analyze.superclass.initComponent.apply(this, arguments);
	},

	onRender:function() {
		TYPO3.EnetcacheAnalytics.logGroupCombo.store = this.logGroupStore;
		TYPO3.EnetcacheAnalytics.logGroupCombo.on('select', function(comboBox, newValue, oldValue) {
			TYPO3.EnetcacheAnalytics.Analyze.logEntryStore.reload({ params: {unique_id: newValue.data.unique_id} });
			TYPO3.EnetcacheAnalytics.Analyze.logStatsStore.reload({ params: {unique_id: newValue.data.unique_id} });
		}, this);
		this.logGroupStore.load({
			callback: function() {
				if (this.getCount() == 0) {
					TYPO3.Flashmessage.display(TYPO3.Severity.warning, 'Warning', 'No log entries found.', 4);
				} else {
					TYPO3.EnetcacheAnalytics.logGroupCombo.setValue(this.getAt(0).data.unique_id);
					TYPO3.EnetcacheAnalytics.Analyze.logEntryStore.reload({ params: {unique_id: this.getAt(0).data.unique_id} });
					TYPO3.EnetcacheAnalytics.Analyze.logStatsStore.reload({ params: {unique_id: this.getAt(0).data.unique_id} });
				}
			}
		});

		TYPO3.EnetcacheAnalytics.Analyze.superclass.onRender.apply(this, arguments);
	}
});
Ext.reg('TYPO3.EnetcacheAnalytics.Analyze', TYPO3.EnetcacheAnalytics.Analyze);


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

TYPO3.EnetcacheAnalytics.Layouts = {
	logStats: function() {
		return new Ext.XTemplate(
			'Plugins rendered:{numberOfPlugins}',
			' ',
			'Using enetcache:{numberOfEnetcachePlugins}',
			' ',
			'Successful Gets:{numberOfSuccessfulGets}'
		);
	}
}