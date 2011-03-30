Ext.ns('TYPO3.EnetcacheAnalytics');

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