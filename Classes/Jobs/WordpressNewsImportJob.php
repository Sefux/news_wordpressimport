<?php
namespace Projektkater\NewsWordpressimport\Jobs;

/***************************************************************
*  Copyright notice
*
*  (c) 2010 Georg Ringer <typo3@ringerge.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
use GeorgRinger\News\Domain\Service\NewsImportService;
use GeorgRinger\News\Jobs\AbstractImportJob;
use Projektkater\NewsWordpressimport\Service\Import\WordpressNewsDataProviderService;

/**
 * Import job
 *
 * @package TYPO3
 * @subpackage news_wordpressimport
 */
class WordpressNewsImportJob extends AbstractImportJob {
	/**
	 * @var int
	 */
	protected $numberOfRecordsPerRun = 30;

	protected $importServiceSettings = array(
		'findCategoriesByImportSource' => 'WP_CATEGORY_IMPORT'
	);
	
	/**
	 * @var WordpressNewsDataProviderService
	 */
	protected $importDataProviderService;

	/**
	 * Inject import dataprovider service
	 *
	 * @param WordpressNewsDataProviderService $importDataProviderService
	 * @return void
	 */
	public function injectImportDataProviderService(WordpressNewsDataProviderService $importDataProviderService) {

		$this->importDataProviderService = $importDataProviderService;
	}

	/**
	 * Inject import service
	 *
	 * @param NewsImportService $importService
	 * @return void
	 */
	public function injectImportService(\GeorgRinger\News\Domain\Service\NewsImportService $importService) {
		$this->importService = $importService;
	}
}