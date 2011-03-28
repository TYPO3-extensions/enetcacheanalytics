Ext.ns('TYPO3.EnetcacheAnalytics.App', 'TYPO3.EnetcacheAnalytics.Analyze');

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
		var store = new Ext.data.DirectStore({
			paramsAsHash: false,
			paramsNames:{uid: "uid"	},
			directFn: TYPO3.EnetcacheAnalytics.Analyze.getEntries,
			root: "",
			idProperty: "uid",
			fields: [
				{name: "uid"},
				{name: "title"},
				{name: "description"}
			]
		});

		var cm = new Ext.grid.ColumnModel({
			columns: [
				{id: 'uid', header: 'Unique ID', width: 30, dataIndex: 'uid'},
				{id: 'title', header: 'Title', width: 160, dataIndex: 'title'},
				{id: 'description', header: 'Description', dataIndex: 'descpription'},
			],
			defaults: {
				sortable:true,
				hideable:false
			}
		});

		Ext.apply(this, {
			store: store,
			cm: cm,
			viewConfig:{forceFit:true, scrollOffset:0}
		});

		TYPO3.EnetcacheAnalytics.Analyze.superclass.initComponent.apply(this, arguments);
	},
	onRender:function() {
		TYPO3.EnetcacheAnalytics.Analyze.superclass.onRender.apply(this, arguments);
		this.store.load();
	}
});


/*
TYPO3.EnetcacheAnalytics.logEntryCombo = new Ext.form.ComboBox({
	id: 'logEntryCombo',
	mode: 'local',
	triggerAction: 'all',
	forceSelection: true,
	editable: false,
	name: 'selectedLogEntry',
	hiddenName: 'selectedLogEntry',
	displayField: 'title',
	valueField: 'unique_id',
	store: null,
	width: 250
});
*/