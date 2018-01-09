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
use \TYPO3\CMS\Core\Utility\PathUtility;
use \TYPO3\CMS\Core\Utility\DebugUtility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

use GeorgRinger\News\Domain\Repository\TagRepository;

/**
 * wordpress ImportService
 *
 * @package TYPO3
 * @subpackage news_wordpressimport
 */
class WordpressNewsDataProviderService implements DataProviderServiceInterface, \TYPO3\CMS\Core\SingletonInterface  {

	protected $importSource = 'WP_NEWS_IMPORT';
	protected $importPid = 21;
	protected $fileStorageUid = 1;
	protected $fileStoragePath = 'archive';
	
	/**
     * @var \GeorgRinger\News\Domain\Repository\TagRepository
     */
    protected $tagRepository;

	/**
	*	constructor
	*/
	public function __construct() {
		$logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
		$this->logger = $logger;
		
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->tagRepository = $objectManager->get('GeorgRinger\News\Domain\Repository\TagRepository');
		
		$emConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['news_wordpressimport']);
		$this->importPid = $emConfiguration['storagePid'];
		$this->fileStorageUid = (int)$emConfiguration['fileStorageUid'];
		$this->fileStoragePath = $emConfiguration['fileStoragePath'];
	}
	
	/**
	 * @param \GeorgRinger\News\Domain\Repository\TagRepository
	 */
	public function injectTagRepository(\GeorgRinger\News\Domain\Repository\TagRepository $tagRepository) {
	  $this->tagRepository = $tagRepository;
	  //$querySettings = $this->tagRepository->createQuery()->getQuerySettings();
	  //$querySettings->setStoragePageIds(array(21));
	  //$this->tagRepository->setDefaultQuerySettings($querySettings);
	}
	
	/**
	 * Get total record count
	 *
	 * @return integer
	 */
	public function getTotalRecordCount() {
		$count = 0;
		
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('wp_posts');
		
		$statement = $queryBuilder
				->count('*')
				->from('wp_posts')    
				->where(
				    $queryBuilder->expr()->eq('post_type', '"POST"'),
					$queryBuilder->expr()->eq('post_status', '"publish"')
				)
				->execute();
		
		$count = $statement->fetchColumn(0);
		
		$this->logger->info(sprintf('START: Counting wordpress posts: %s ', $count));
		//$sql = $queryBuilder->getSQL();
		//$this->logger->info(sprintf('SQL:   "%s" ', $sql));
		
		//return 10;
		return (int)$count;
	}

	/**
	 * Get the partial import data, based on offset + limit
	 *
	 * @param integer $offset offset
	 * @param integer $limit limit
	 * @return array
	 */
	public function getImportData($offset = 0, $limit = 50) {
		$importData = array();
		
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('wp_posts');
		
		$statement = $queryBuilder->select('*')
    			->from('wp_posts')    
				->where(
					$queryBuilder->expr()->eq('post_type', '"POST"'),
					$queryBuilder->expr()->eq('post_status', '"publish"')
				)
				->orderBy('post_date')
				->setMaxResults($limit)
   				->setFirstResult($offset)
    			->execute();
		$sql = $queryBuilder->getSQL();
		//$this->logger->info(sprintf('IMPORT POSTS SQL:   "%s" ', $sql));

		while ($row = $statement->fetch()) {
			$importData[] = array(
				'pid' => $this->importPid,
				'hidden' => ($row['post_status']=="publish" ? 0 : 1),
				'tstamp' => strtotime($row['post_modified']),
				'crdate' => strtotime($row['post_date']),
				'cruser_id' => $row['post_author'],
				//'l10n_parent' => $row['l18n_parent'],
				//'sys_language_uid' => $row['sys_language_uid'],
				//'sorting' => array_key_exists('sorting', $row) ? $row['sorting'] : 0,
				//'starttime' => $row['starttime'],
				//'endtime'  => $row['endtime'],
				//'fe_group'  => $row['fe_group'],
				'title' => $row['post_title'],
				'teaser' => $row['post_excerpt'],
				'bodytext' => str_replace('###YOUTUBEVIDEO###', '', $row['post_content']),
				'datetime' => strtotime($row['post_date']),
				//'archive' => $row['archivedate'],
				'author' => $row['post_author'],
				//'author_email' => $row['author_email'],
				'type' => 0,//$row['type'],
				//'keywords' => $row['keywords'],
				//'externalurl' => $row['ext_url'],
				//'internalurl' => $row['page'],
				'tags' => $this->getTags($row['ID']),
				'categories' => $this->getCategories($row['ID']),
				'media' => $this->getMedia($row),
				//'related_files' => $this->getFiles($row),
				//'related_links' => array_key_exists('tx_tlnewslinktext_linktext', $row) ? $this->getRelatedLinksTlNewsLinktext($row['links'], $row['tx_tlnewslinktext_linktext']) : $this->getRelatedLinks($row['links']),
				//'content_elements' => $row['tx_rgnewsce_ce'],
				'import_id' => $row['ID'],
				'import_source' => $this->importSource
			);
		}
		
		return $importData;
		//return array();
	}

	/**
	 * Get correct categories of a news record
	 *
	 * @param integer $newsUid news uid
	 * @return array
	 */
	protected function getCategories($newsUid) {
		$categories = array();

		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('wp_term_relationships');
		$statement = $queryBuilder->select('wp_term_relationships.term_taxonomy_id as term_taxonomy_id')
				->from('wp_term_relationships') 
				->join(
					'wp_term_relationships',
					'wp_term_taxonomy',
					'tt',
					$queryBuilder->expr()->eq('wp_term_relationships.term_taxonomy_id', $queryBuilder->quoteIdentifier('tt.term_taxonomy_id'))
					) 
				->where(
					$queryBuilder->expr()->eq('wp_term_relationships.object_id', $newsUid),
					$queryBuilder->expr()->eq('tt.taxonomy', '"category"')
				)
				->execute();
		$sql = $queryBuilder->getSQL();
		$this->logger->info(sprintf('IMPORT CATEGORIE SQL:   "%s" ', $sql));
	
		while ($row = $statement->fetch()) {
			$categories[] = $row['term_taxonomy_id'];
		}
		$this->logger->info(sprintf('IMPORT CATEGORIE FOR UID:   "%s" ', $newsUid), $categories);

		return $categories;
	}

	/**
	 * Get correct tags of a news record
	 *
	 * @param integer $newsUid news uid
	 * @return array
	 */
	protected function getTags($newsUid) {
		$tags = array();
		
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('wp_term_relationships');
		
		$statement = $queryBuilder->select('wp_terms.name as name')
    			->from('wp_terms')
				->join(
					'wp_terms',
					'wp_term_taxonomy',
					'tt',
					$queryBuilder->expr()->eq('wp_terms.term_id', $queryBuilder->quoteIdentifier('tt.term_id'))
					) 
				->join(
					'tt',
					'wp_term_relationships',
					'wp_term_relationships',
					$queryBuilder->expr()->eq('wp_term_relationships.term_taxonomy_id', $queryBuilder->quoteIdentifier('tt.term_taxonomy_id'))
					) 	
				->where(
					$queryBuilder->expr()->eq('tt.taxonomy', '"post_tag"'),
					$queryBuilder->expr()->eq('wp_term_relationships.object_id', $newsUid)
				)
				->execute();
			
		//TODO: remove this mieser hack... this (nad other repository function) does not work
		//$tag = $this->tagRepository->findByTitle('2012');
		$whereExpressions = array();
		$where = '';
		while ($row = $statement->fetch()) {
			//$whereExpressions[] = $tagQueryBuilder->expr()->eq('title', '"'.$row['name'].'"');
			$where .= ' OR title="'.$row['name'].'"';
		}
		//if(count($whereExpressions)) {
		if($where != '') {
			$tagQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_news_domain_model_tag');
			$tagStatement = $tagQueryBuilder->select('*')
				->from('tx_news_domain_model_tag')
				->where(
					'uid = 0 '.$where
				)
				->execute();
			//$sql = $tagQueryBuilder->getSQL();	
			//$this->logger->info('TAGREPO : '.$sql, (array) $tagRow['uid']);
			while ($tagRow = $tagStatement->fetch()) {
				$tags[] = $tagRow['uid'];
			}
		}	
		return $tags;
	}
	
	/**
	 * Get correct media elements to be imported
	 *
	 * @param array $row news record
	 * @return array
	 */
	protected function getMedia(array $row) {
		$media = array();

		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('wp_postmeta');
		$statement = $queryBuilder->select('*')
    			->from('wp_postmeta') 
				->join(
					'wp_postmeta',
					'wp_posts',
					'image',
					$queryBuilder->expr()->eq(
					 'wp_postmeta.meta_value',
					 $queryBuilder->quoteIdentifier('image.ID')
				 	),
					$queryBuilder->expr()->eq('post_type', '"attachment"')
				) 
				->where(
					$queryBuilder->expr()->eq('meta_key', '"_thumbnail_id"'),
					$queryBuilder->expr()->eq('post_id', $row['ID'])
				)
    			->execute();
				$queryBuilderMeta = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('wp_postmeta');
				$meta_statement = $queryBuilderMeta->select('wp_postmeta.*')
		    			->from('wp_postmeta') 
						->join(
							'wp_postmeta',
							'wp_postmeta',
							'parentmeta',
							$queryBuilderMeta->expr()->eq(
							 'wp_postmeta.post_id',
							 $queryBuilderMeta->quoteIdentifier('parentmeta.meta_value')
						 	)
						) 
						->where(
							$queryBuilderMeta->expr()->eq('parentmeta.meta_key', '"_thumbnail_id"'),
							$queryBuilderMeta->expr()->eq('wp_postmeta.meta_key', '"_wp_attached_file"'),
							$queryBuilderMeta->expr()->eq('parentmeta.post_id', $row['ID'])
						)
		    			->execute();
					$meta_row = $meta_statement->fetch();
						
		//$sql = $queryBuilderMeta->getSQL();
		//$this->logger->info(sprintf('SEARCH IMAGE SQL:   "%s" ', $sql));
		while ($image = $statement->fetch()) {
			$media[] = array(
				'title' => $image['post_title'],
				'alt' => $image['post_excerpt'],
				'caption' => $image['post_content'],
				'image' => $this->fileStorageUid . ":" . $this->fileStoragePath . "/" . $meta_row['meta_value'],
				'type' => 0,
				'showinpreview' => 1
			);
		}
		$this->logger->info(sprintf('FOUND MEDIA:   "%s" ', $row['ID']), $media);
		return $media;
	}
}
