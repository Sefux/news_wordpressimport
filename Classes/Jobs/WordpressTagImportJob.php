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
use GeorgRinger\News\Jobs\AbstractImportJob;

/**
 * Import job
 *
 * @package TYPO3
 * @subpackage news_wordpressimport
 */
class WordpressTagImportJob extends AbstractImportJob {

	/**
	 * Inject import dataprovider service
	 *
	 * @param \Projektkater\NewsWordpressimport\Service\Import\WordpressTagDataProviderService $importDataProviderService
	 * @return void
	 */
	public function injectImportDataProviderService(\Projektkater\NewsWordpressimport\Service\Import\WordpressTagDataProviderService
		$importDataProviderService) {

		$this->importDataProviderService = $importDataProviderService;
	}

	/**
	 * Inject import service
	 *
	 * @param \Projektkater\NewsWordpressimport\Service\Import\WordpressTagImportService $importService
	 * @return void
	 */
	public function injectImportService(\Projektkater\NewsWordpressimport\Service\Import\WordpressTagImportService $importService) {
		$this->importService = $importService;
	}
}