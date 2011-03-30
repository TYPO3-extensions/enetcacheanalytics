TYPO3.EnetcacheAnalytics.Performance = Ext.extend(Ext.Panel, {
	layout: 'border',

	initComponent:function() {
		/**
		 * Display and handle available and selected grids,
		 * this is a drap + drop setup
		 */
		this.availableTestsStore = new Ext.data.DirectStore({
			storeId: 'availableTests',
			idProperty: 'name',
			directFn: TYPO3.EnetcacheAnalytics.Analyzer.getNotEnabledTestEntries,
			root: 'data',
			totalProperty: 'length',
			fields: ['name'],
			paramsAsHash: true,
			paramNames: {
				unique_id: 'name'
			}
		});
		this.selectedTestsStore = new Ext.data.DirectStore({
			storeId: 'enabledTests',
			idProperty: 'name',
			directFn: TYPO3.EnetcacheAnalytics.Analyzer.getEnabledTestEntries,
			root: 'data',
			totalProperty: 'length',
			fields: ['name'],
			paramsAsHash: true,
			paramNames: {
				unique_id: 'name'
			}
		});
		var cm = new Ext.grid.ColumnModel({
			columns: [
				{id: 'name', header: 'Name', dataIndex: 'name'}
			],
			defaults: {
				sortable: false,
				menuDisabled: true,
				hideable: false
			}
		});
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
							var record =  ddSource.dragData.selections;
							Ext.each(record, ddSource.grid.store.remove, ddSource.grid.store);
							TYPO3.EnetcacheAnalytics.Performance.availableTestsGrid.store.add(record);
							TYPO3.EnetcacheAnalytics.Performance.availableTestsGrid.store.sort('uid', 'ASC');
	                        var ucName = 'moduleData.enetcacheanalytics.performance.enabledTests.' + record[0].data.name;
							TYPO3.BackendUserSettings.ExtDirect.set(
								ucName,
								0,
								function(response) {}
							);
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
							var record =  ddSource.dragData.selections;
							Ext.each(record, ddSource.grid.store.remove, ddSource.grid.store);
							TYPO3.EnetcacheAnalytics.Performance.selectedTestsGrid.store.add(record);
							TYPO3.EnetcacheAnalytics.Performance.selectedTestsGrid.store.sort('uid', 'ASC');
	                        var ucName = 'moduleData.enetcacheanalytics.performance.enabledTests.' + record[0].data.name;
							TYPO3.BackendUserSettings.ExtDirect.set(
								ucName,
								1,
								function(response) {}
							);
							return true
                        }
                    });
				}
			}
		});

		/**
		 * Display and handle events on available backends
		 */
		this.backendsSelectionModel  = new Ext.grid.CheckboxSelectionModel({
			singleSelect: false,
			header: '',
			dataIndex: 'selected',
			checkOnly: false,
			listeners: {
				rowselect: function(sm, index, record) {
					var name = 'moduleData.enetcacheanalytics.performance.enabledBackends.' + record.data.name;
					TYPO3.BackendUserSettings.ExtDirect.set(
						name,
                        1,
                        function(response) {}
					);
				},
				rowdeselect: function(sm, index, record) {
					var name = 'moduleData.enetcacheanalytics.performance.enabledBackends.' + record.data.name;
						// @TODO: Use unsetKey, but core doesn't handle dotted notation for this method
					TYPO3.BackendUserSettings.ExtDirect.set(
						name,
                        0,
                        function(response) {}
					);
				}
			}
		});
		var backendsGridCM = new Ext.grid.ColumnModel({
			columns: [
				this.backendsSelectionModel,
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
			fields: ['selected', 'name'],
			paramsAsHash: true,
			paramNames: {
				unique_id: 'name'
			},
			listeners : {
				'load': function(store, records) {
						// get selected backends to update selection
					var a = [];
					for (var i=0; i<records.length; i++) {
						if(records[i].data.selected) {
							a.push(records[i]);
						}
					}
					this.backendsSelectionModel.selectRecords(a);
				},
				scope: this
			}
		});
		TYPO3.EnetcacheAnalytics.Performance.backendsGrid = new Ext.grid.GridPanel({
			store: this.backendsStore,
			cm: backendsGridCM,
			sm: this.backendsSelectionModel,
			autoExpandColumn: 'name',
			autoHeight: true
		});

		/**
		 * Display and handle events on settings (scaleFactor and dataPoints)
		 */
		TYPO3.EnetcacheAnalytics.Performance.settingsForm = new Ext.form.FormPanel({
			autoHeight: true,
			id: 'parameterForm',
			items: [{
				name: 'dataPoints',
				fieldLabel: 'Number of data points',
				xtype: 'numberfield',
				allowBlank: true,
				listeners: {
					change: function(field, newValue, oldValue) {
						TYPO3.EnetcacheAnalytics.Performance.selectedTestsGrid.disable();
						TYPO3.BackendUserSettings.ExtDirect.set(
							'moduleData.enetcacheanalytics.performance.settings.dataPoints',
                            newValue,
                            function(response) {
	                            TYPO3.EnetcacheAnalytics.Performance.selectedTestsGrid.enable();
                            }
						);
					},
					beforerender: function(field) {
						field.value = TYPO3.settings.enetcacheAnalytics.performance.settings.dataPoints;
					},
					scope: this
				}
			},{
				name: 'scaleFactor',
				fieldLabel: 'Scale factor',
				xtype: 'numberfield',
				allowBlank: true,
				listeners: {
					change: function(field, newValue, oldValue) {
						TYPO3.EnetcacheAnalytics.Performance.selectedTestsGrid.disable();
						TYPO3.BackendUserSettings.ExtDirect.set(
							'moduleData.enetcacheanalytics.performance.settings.scaleFactor',
                            newValue,
                            function(response) {
	                            TYPO3.EnetcacheAnalytics.Performance.selectedTestsGrid.enable();
                            }
						);
					},
					beforerender: function(field) {
						field.value = TYPO3.settings.enetcacheAnalytics.performance.settings.scaleFactor;
					},
					scope: this
				}
			}]
		});

		/**
		 * Compile performance module
		 */
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
					TYPO3.EnetcacheAnalytics.Performance.settingsForm,
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
		this.selectedTestsStore.load();
		this.backendsStore.load();

		TYPO3.EnetcacheAnalytics.Performance.superclass.onRender.apply(this, arguments);
	}
});

Ext.reg('TYPO3.EnetcacheAnalytics.Performance', TYPO3.EnetcacheAnalytics.Performance);
