Ext.ns('TYPO3.EnetcacheAnalytics');

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