# Logging tables

# This table groups all events from one page FE-Run by id
CREATE TABLE tx_enetcache_log (
	uid int(11) unsigned NOT NULL auto_increment

	be_user int(11) DEFAULT '0' NOT NULL ,
	caller varchar(300) DEFAULT '0' NOT NULL ,
	content_uid int(11) DEFAULT '0' NOT NULL,
	data longtext,
	fe_user int(11) DEFAULT '0' NOT NULL,
	identifier_source longtext,
	identifier varchar(32) DEFAULT '0' NOT NULL,
	lifetime int(11) unsigned DEFAULT '0' NOT NULL,
	microtime bigint(64) DEFAULT '0' NOT NULL,
	page_uid int(11) DEFAULT '0' NOT NULL,
	request_type tinytext,
	tags longtext,
	tstamp int(11) DEFAULT '0' NOT NULL,
	unique_id text,
	
	PRIMARY KEY (uid)
) ENGINE=InnoDB;
