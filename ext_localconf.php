<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE === 'BE') {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Projektkater\\NewsWordpressimport\\Command\\WordpressPluginMigrateCommandController';
	
	//$GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Repository/TagRepository'][] = 'news_wordpressimport';
}
