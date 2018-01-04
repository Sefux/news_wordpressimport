<?php
namespace Projektkater\NewsWordpressimport\Service\Import;

/***************************************************************
*  Copyright notice
*
*  (c) 2011 Nikolas Hagelstein <nikolas.hagelstein@gmail.com>
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
use GeorgRinger\News\Service\Import\DataProviderServiceInterface;

/**
 * tt_news category ImportService
 *
 * @package TYPO3
 * @subpackage news_wordpressimport
 */
class WordpressCategoryDataProviderService implements DataProviderServiceInterface, \TYPO3\CMS\Core\SingletonInterface {

	protected $importSource = 'WP_CATEGORY_IMPORT';
	protected $importPid = 21;

	/**
	 * Get total count of category records
	 *
	 * @return integer
	 */
	public function getTotalRecordCount() {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(*)',
			'wp_term_taxonomy as tt LEFT JOIN wp_terms as t ON tt.term_id = t.term_id',
			'tt.taxonomy="category" ' // OR tt.taxonomy="post_tag"
		);

		list($count) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return (int)$count;
	}

	/**
	 * Get the partial import data, based on offset + limit
	 *
	 * @param integer $offset offset
	 * @param integer $limit limit
	 * @return array
	 */
	public function getImportData($offset = 0, $limit = 200) {
		$importData = array();

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',
			'wp_term_taxonomy as tt LEFT JOIN wp_terms as t ON tt.term_id = t.term_id',
			'tt.taxonomy="category"' //  OR tt.taxonomy="post_tag"
			'',
			'',
			$offset . ',' . $limit
		);

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$importData[] = array(
				'uid' => $row['term_taxonomy_id'],
				'pid' => $this->importPid,
				//'hidden' => $row['hidden'],
				'title'	=>	$row['name'],
				'description' => $row['description'],
				'image' => $row['image'] ? 'uploads/pics/' . $row['image'] : '',
				'shortcut' => $row['shortcut'],
				'single_pid' => $row['single_pid'],
				'parentcategory' => $row['parent'],
				'import_id' =>  $row['term_taxonomy_id'],
				'import_source' => $this->importSource
			);
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return $importData;
	}
}