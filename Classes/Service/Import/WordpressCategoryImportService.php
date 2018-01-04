<?php
namespace Projektkater\NewsWordpressimport\Service\Import;
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
use GeorgRinger\News\Domain\Service\CategoryImportService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * tt_news category import service
 *
 * @package TYPO3
 * @subpackage tx_news
 * @author Lorenz Ulrich <lorenz.ulrich@visol.ch>
 */
class WordpressCategoryImportService extends CategoryImportService {

	/**
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $databaseConnection;

	public function __construct() {
		parent::__construct();
		$this->databaseConnection = $GLOBALS['TYPO3_DB'];
	}

	public function import(array $importArray) {
		// import categories
		parent::import($importArray);

	}

}
