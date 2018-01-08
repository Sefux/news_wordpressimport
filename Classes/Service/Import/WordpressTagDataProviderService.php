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
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Utility\DebugUtility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/**
 * tt_news tag ImportService
 *
 * @package TYPO3
 * @subpackage news_wordpressimport
 */
class WordpressTagDataProviderService implements DataProviderServiceInterface, \TYPO3\CMS\Core\SingletonInterface {

	protected $importSource = 'WP_TAG_IMPORT';
	protected $importPid = 21;
	
	public function __construct() {
        $logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        $this->logger = $logger;
    }

	/**
	 * Get total count of tag records
	 *
	 * @return integer
	 */
	public function getTotalRecordCount() {
		
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('wp_posts');
		
		$statement = $queryBuilder
				->count('wp_term_taxonomy.term_id')
				->from('wp_term_taxonomy')   
				->join(
					'wp_term_taxonomy',
					'wp_terms',
					't',
					$queryBuilder->expr()->eq(
					 'wp_term_taxonomy.term_id',
					 $queryBuilder->quoteIdentifier('t.term_id')
					)
					) 
				->where(
					$queryBuilder->expr()->eq('wp_term_taxonomy.taxonomy', '"post_tag"')
				)
				->execute();
		$sql = $queryBuilder->getSQL();
		$count = $statement->fetchColumn(0);
		
		$this->logger->info(sprintf('START: Counting wordpress tags: %s ', $count));
		
		$this->logger->info(sprintf('SQL:   "%s" ', $sql));
		
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

		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('wp_term_taxonomy');
		
		$statement = $queryBuilder
				->select('*')
				->from('wp_term_taxonomy')   
				->join(
					'wp_term_taxonomy',
					'wp_terms',
					't',
					$queryBuilder->expr()->eq(
					 'wp_term_taxonomy.term_id',
					 $queryBuilder->quoteIdentifier('t.term_id')
					)
					) 
				->where(
					$queryBuilder->expr()->eq('wp_term_taxonomy.taxonomy', '"post_tag"')
				)
				->setMaxResults($limit)
   				->setFirstResult($offset)
				->execute();
		$sql = $queryBuilder->getSQL();
		$this->logger->info(sprintf('IMPORT CATEGROIES SQL:   "%s" ', $sql));

		while ($row = $statement->fetch()) {
			$this->logger->info('IMPORT DATA ROW', $row);
			$importData[] = array(
				'uid' => $row['term_taxonomy_id'],
				'pid' => $this->importPid,
				//'hidden' => $row['hidden'],
				'title'	=>	$row['name'],
				'description' => $row['description'],
				'import_id' =>  $row['term_taxonomy_id'],
				'import_source' => $this->importSource
			);
		}
		

		return $importData;
	}
}