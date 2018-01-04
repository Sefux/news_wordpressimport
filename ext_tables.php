<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\GeorgRinger\News\Utility\ImportJob::register(
	'Projektkater\\NewsWordpressimport\\Jobs\\WordpressNewsImportJob',
	'LLL:EXT:news_wordpressimport/Resources/Private/Language/locallang_be.xml:wordpress_importer_title',
	'');
\GeorgRinger\News\Utility\ImportJob::register(
	'Projektkater\\NewsWordpressimport\\Jobs\\WordpressCategoryImportJob',
	'LLL:EXT:news_wordpressimport/Resources/Private/Language/locallang_be.xml:wordpresscategory_importer_title',
	'');
\GeorgRinger\News\Utility\ImportJob::register(
	'Projektkater\\NewsWordpressimport\\Jobs\\MblNewseventImportJob',
	'LLL:EXT:news_wordpressimport/Resources/Private/Language/locallang_be.xml:mblnewsevent_importer_title',
	'');
\GeorgRinger\News\Utility\ImportJob::register(
	'Projektkater\\NewsWordpressimport\\Jobs\\DamMediaTagConversionJob',
	'LLL:EXT:news_wordpressimport/Resources/Private/Language/locallang_be.xml:dammediatag_converter_title',
	'');
