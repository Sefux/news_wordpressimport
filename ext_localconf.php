<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE === 'BE') {	
	//$GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Repository/TagRepository'][] = 'news_wordpressimport';
	
	\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
		\GeorgRinger\News\Domain\Service\NewsImportService::class,
		'postHydrate',
		\Projektkater\NewsWordpressimport\Aspect\NewsImportAspect::class,
		'postHydrate'
	);
}
