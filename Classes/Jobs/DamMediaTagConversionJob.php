<?php
namespace Projektkater\NewsWordpressimport\Jobs;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use GeorgRinger\News\Jobs\AbstractImportJob;

/**
 * Import job
 *
 * @package TYPO3
 * @subpackage news_wordpressimport
 */
class DamMediaTagConversionJob extends AbstractImportJob {
	/**
	 * @var int
	 */
	protected $numberOfRecordsPerRun = 30;

	/**
	 * Inject import dataprovider service
	 *
	 * @param \Projektkater\NewsWordpressimport\Service\Import\DamMediaTagDataProviderService $importDataProviderService
	 * @return void
	 */
	public function injectImportDataProviderService(\Projektkater\NewsWordpressimport\Service\Import\DamMediaTagDataProviderService
		$importDataProviderService) {

		$this->importDataProviderService = $importDataProviderService;
	}

	/**
	 * Inject import service
	 *
	 * @param \Projektkater\NewsWordpressimport\Service\Import\DamMediaTagConversionService $importService
	 * @return void
	 */
	public function injectImportService(\Projektkater\NewsWordpressimport\Service\Import\DamMediaTagConversionService $importService) {
		$this->importService = $importService;
	}
}