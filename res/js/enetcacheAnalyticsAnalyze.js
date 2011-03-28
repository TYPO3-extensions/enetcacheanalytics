Ext.ns('TYPO3.EnetcacheAnalytics');

TYPO3.EnetcacheAnalytics.Analyze = {
	init : function() {

		var storeFields = [ {
			name : "uid"
		}, {
			name : "title"
		}, {
			name : "description"
		} ];

		var store = new Ext.data.DirectStore({
			paramsAsHash : false,
			paramsNames : {
				uid : "uid"
			},

			directFn : TYPO3.EnetcacheAnalytics.Analyze.getEntries,
			root : "",
			idProperty : "uid",
			fields : storeFields
		});

		// manually load local data
		store.load();

		// create the Grid
		var grid = new Ext.grid.GridPanel({
			store : store,
			columns : [ {
				id : "uid",
				header : "Unique ID",
				width : 30,
				sortable : true,
				dataIndex : "uid"
			}, {
				id : "title",
				header : "Title",
				width : 160,
				sortable : true,
				dataIndex : "title"
			}, {
				header : "Description",
				// width : auto,
				sortable : true,
				dataIndex : "description"
			} ],
			stripeRows : true,
			autoExpandColumn : "uid",
			height : 350,
			width : 500,
			title : "Array Grid",
			// config options for stateful behavior
			stateful : true,
			stateId : "grid"
		});

		grid.render("tx-enetcacheanalytics-mod-grid");
	}

}