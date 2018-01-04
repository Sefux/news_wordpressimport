<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "news_wordpressimport"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'wordpress importer',
	'description' => 'Importer of wordpress tables to ext:news',
	'category' => 'be',
	'author' => 'fux',
	'author_email' => 'sfuchs@projektkater.de',
	'company' => 'projektkater',
	'shy' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'version' => '2.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.4-8.99.99',
			'php' => '5.3.0-0.0.0',
			'news' => '3.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);
