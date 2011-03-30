TYPO3.EnetcacheAnalytics.Performance = Ext.extend(Ext.Panel, {
	layout: 'border',

	initComponent:function() {
		this.storeFields = [
			'uid', 'name'
		];

		this.availableTestsStore = new Ext.data.DirectStore({
			storeId: 'availableTests',
			idProperty: 'uid',
			directFn: TYPO3.EnetcacheAnalytics.Analyzer.getTestEntries,
			root: 'data',
			totalProperty: 'length',
			fields: this.storeFields,
			paramsAsHash: true,
			paramNames: {
				unique_id: 'uid'
			}
		});
		
		this.selectedTestsStore = new Ext.data.JsonStore({
			fields: this.storeFields,
			root: 'data'
		});

		var cm = new Ext.grid.ColumnModel({
			columns: [
				{id: 'uid', header: 'UID', dataIndex: 'uid', width: 16},
				{id: 'name', header: 'Name', dataIndex: 'name'}
			],
			defaults: {
				sortable: false,
				menuDisabled: true,
				hideable: false
			}
		});

		var cols = [
			{id: 'uid', header: "uid", dataIndex: 'uid'},
			{id: 'name', header: 'Name', dataIndex: 'name'}
		];

		TYPO3.EnetcacheAnalytics.Performance.availableTestsGrid = new Ext.grid.GridPanel({
			ddGroup: 'selectedTestsGridDDGroup',
			store: this.availableTestsStore,
			cm: cm,
			enableDragDrop: true,
			stripeRows: true,
			autoExpandColumn: 'name',
			autoHeight: true,


			listeners: {
				scope: this,
				afterRender: function(grid) {
					var availableTestsGridDropTargetEl =  TYPO3.EnetcacheAnalytics.Performance.availableTestsGrid.getView().scroller.dom;
                    var availableTestsGridDropTarget = new Ext.dd.DropTarget(availableTestsGridDropTargetEl, {
                        ddGroup    : 'availableTestsGridDDGroup',
                        notifyDrop : function(ddSource, e, data) {
							var records =  ddSource.dragData.selections;
							Ext.each(records, ddSource.grid.store.remove, ddSource.grid.store);
							TYPO3.EnetcacheAnalytics.Performance.availableTestsGrid.store.add(records);
							TYPO3.EnetcacheAnalytics.Performance.availableTestsGrid.store.sort('uid', 'ASC');
							return true
                        }
                    });
				}
			}

		});

		TYPO3.EnetcacheAnalytics.Performance.selectedTestsGrid = new Ext.grid.GridPanel({
			ddGroup: 'availableTestsGridDDGroup',
			store: this.selectedTestsStore,
			cm: cm,
			enableDragDrop: true,
			stripeRows: true,
			autoExpandColumn: 'name',

			listeners: {
				scope: this,
				afterRender: function(grid) {
					var selectedTestsGridDropTargetEl =  TYPO3.EnetcacheAnalytics.Performance.selectedTestsGrid.getView().scroller.dom;
                    var selectedTestsGridDropTarget = new Ext.dd.DropTarget(selectedTestsGridDropTargetEl, {
                        ddGroup: 'selectedTestsGridDDGroup',
                        notifyDrop: function(ddSource, e, data) {
							var records =  ddSource.dragData.selections;
							Ext.each(records, ddSource.grid.store.remove, ddSource.grid.store);
							TYPO3.EnetcacheAnalytics.Performance.selectedTestsGrid.store.add(records);
							TYPO3.EnetcacheAnalytics.Performance.selectedTestsGrid.store.sort('uid', 'ASC');
							return true
                        }
                    });
				}
			}
		});

		this.backendsSelectionModel  = new Ext.grid.CheckboxSelectionModel({
			singleSelect: false,
			header: '',
			dataIndex: 'selected',
			checkOnly: false

		});
		var backendsGridCM = new Ext.grid.ColumnModel({
			columns: [
				this.backendsSelectionModel,
				{id: 'uid', header: "uid", dataIndex: 'uid'},
				{id: 'name', header: 'Name', dataIndex: 'name'}
			],
			defaults: {
				sortable: false,
				menuDisabled: true,
				hideable: false
			}
		});
		this.backendsStore = new Ext.data.DirectStore({
			storeId: 'backends',
			idProperty: 'uid',
			directFn: TYPO3.EnetcacheAnalytics.Analyzer.getBackends,
			root: 'data',
			totalProperty: 'length',
			fields: ['uid', 'name'],
			paramsAsHash: true,
			paramNames: {
				unique_id: 'uid'
			}
		});
		TYPO3.EnetcacheAnalytics.Performance.backendsGrid = new Ext.grid.GridPanel({
			store: this.backendsStore,
			cm: backendsGridCM,
			sm: this.backendsSelectionModel,
			autoExpandColumn: 'name',
			autoHeight: true
//			onRowClick: Ext.emptyFn
		});


		Ext.apply(this, {
			items: [{
				region: 'west',
				layout: 'fit',
				frame: true,
				border: false,
				width: 400,
				split: true,
				collapsible: true,
				collapseMode: 'mini',
				items: [
					TYPO3.EnetcacheAnalytics.Performance.backendsGrid,
					TYPO3.EnetcacheAnalytics.Performance.availableTestsGrid
				]
			},{
				region: 'center',
				layout: 'fit',
				frame: true,
				border: false,
				items: [
					TYPO3.EnetcacheAnalytics.Performance.selectedTestsGrid
				]
			}]
		});

		TYPO3.EnetcacheAnalytics.Performance.superclass.initComponent.apply(this, arguments);
	},

	onRender:function() {
		this.availableTestsStore.load();
		this.backendsStore.load();

		TYPO3.EnetcacheAnalytics.Performance.superclass.onRender.apply(this, arguments);
	}
});

Ext.reg('TYPO3.EnetcacheAnalytics.Performance', TYPO3.EnetcacheAnalytics.Performance);
