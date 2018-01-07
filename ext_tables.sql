#
# Table structure for table 'tx_news_domain_model_tag'
#
CREATE TABLE tx_news_domain_model_tag (
	import_id varchar(100) DEFAULT '' NOT NULL,
	import_source varchar(100) DEFAULT '' NOT NULL
);


#
# Table structure for table 'tt_content'
#
#
CREATE TABLE tt_content (
	news_wordpressimport_new_id int(11) DEFAULT '0' NOT NULL
);